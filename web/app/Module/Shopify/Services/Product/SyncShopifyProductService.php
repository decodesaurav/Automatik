<?php
namespace App\Module\Shopify\Services\Product;
use App\Exceptions\ShopifyApiRateLimitException;
use App\Jobs\SyncShopifyProducts;
use App\Jobs\SyncShopifyProductsVariantInventories;
use App\Models\Session;
use App\Models\ShopifyProduct;
use App\Models\ShopifyProductVariant;
use App\Models\ShopifyVariationInventory;
use App\Module\Shopify\Helper\ShopifyHelper;
use App\Module\Shopify\Repositories\Collection\ShopifyCollectionRepository;
use App\Module\Shopify\Repositories\Product\ShopifyProductRepository;
use App\Module\Shopify\Services\Collection\SyncShopifyCollectionService;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Log;
use Psr\Http\Client\ClientExceptionInterface;
use Shopify\Clients\HttpResponse;
use Shopify\Clients\Rest;

class SyncShopifyProductService
{
	protected ShopifyProductRepository $shopifyProductRepository;

	protected ShopifyProductDataService $shopifyProductDataService;

	public function __construct(
		ShopifyProductRepository $repo,
		ShopifyProductDataService $dataService
	) {
		$this->shopifyProductRepository = $repo;
		$this->shopifyProductDataService = $dataService;
	}

	public function setJob($shop, $onQueue = 'shopify_sync')
	{
		try {
			$totalShopifyProductCount = $this->fetchShopifyProductCount($shop);
			if (!$totalShopifyProductCount) {
				$this->finalizeProductSync($shop);
				return;
			}
			$query = $this->getQueryForSyncShopifyProductJob($shop);
			if ($onQueue === "shopify_initial_sync") {
				$shop->update([
					'shopify_last_import_at' => Carbon::parse(now())->toISOString()
				]);
			}
			SyncShopifyProducts::dispatch($shop, $query)->onQueue($onQueue);
		} catch (\Exception | ClientExceptionInterface $e) {
			Log::channel("daily")
				->error("Error while setting job to sync shopify product", [
					'Shop' => $shop,
					'Message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine()
				]);
		}
	}

	public function fetchShopifyProductCount($shop)
	{
		$query = [
			'status' => 'active'
		];
		if ($shop->shopify_last_import_at) {
			$date = new DateTime($shop->shopify_last_import_at, new DateTimeZone('UTC'));
			$query['updated_at_min'] = $date->format('c');
		}
		$client = new Rest($shop->shop, $shop->access_token);
		$result = $client->get('products/count', [], $query);
		$result = $result->getDecodedBody();

		if (isset($result['count'])) {
			Log::channel('daily')->info("$shop->shop Total shopify products Count:" . $result['count'] . " query:" . json_encode($query));
			return $result['count'];
		}
		if (isset($result['errors'])) {
			Log::channel('daily')->info("Error while fetching products count for $shop->shop. " . json_encode($result));
			if ($result['errors'] === "Unavailable Shop" || $result['errors'] === "Not Found") {
				return 0;
			}
		}
		return 0;
	}
	private function finalizeProductSync(Session $shop): void
	{
		$syncShopifyCollectionService = new SyncShopifyCollectionService(new ShopifyCollectionRepository());
		$now = Carbon::parse(now())->toISOString();
		$syncShopifyCollectionService->setJob($shop);
		$shop->update([
			'last_collection_import_at' => $now,
		]);
	}

	private function getQueryForSyncShopifyProductJob(Session $shop): array
	{
		$query = [
			'limit' => '250',
			'status' => 'active',
		];
		if ($shop->shopify_last_import_at) {
			$date = new DateTime($shop->shopify_last_import_at, new DateTimeZone('UTC'));
			$query['updated_at_min'] = $date->format('c');
		}
		return $query;
	}

	public function syncShopifyProduct(Session $shop, $query, bool $isShopifyInitialSync)
	{
		$result = $this->fetchShopifyProducts($shop, $query);
		if (!$result) {
			Log::channel('daily')->info("Result was empty for $shop->shop");
			$this->finalizeProductSync($shop);
		}
		$products = $result->getDecodedBody()['products'] ?? [];
		if (empty($products)) {
			$this->finalizeProductSync($shop);
		}
		$jobs = [];
		$item_level_ids = [];

		foreach ($products as $productFromShopify) {
			$data = $this->shopifyProductDataService->getFormattedProductDataToStore($productFromShopify, $shop->shop);
			$data['shopify_session_id'] = $shop->session_id;
			$product = $this->shopifyProductRepository->findAndUpdateOrCreateShopifyProduct([
				'session_id' => $shop->id,
				'shopify_product_id' => $productFromShopify['id']
			], $data);
			$this->formatAndCreateVariation($productFromShopify, $shop, $product, $item_level_ids, $isShopifyInitialSync);
		}
		Log::channel(channel: 'daily')->info("Item Level data" . json_encode($item_level_ids));

		if (count($item_level_ids)) {
			$item_level_ids_chunk = array_chunk($item_level_ids, 50);
			foreach ($item_level_ids_chunk as $key => $inv_level_data) {
				if (count($inv_level_data)) {
					Log::channel(channel: 'daily')->info("Inventory level data" . json_encode($inv_level_data));
					$is_last = false;
					if ($key === array_key_last($item_level_ids_chunk)) {
						$is_last = true;
					}
					$inventory_level_query['inventory_item_ids'] = implode(',', $inv_level_data);
					Log::channel(channel: 'daily')->info("Inventories query" . json_encode($inventory_level_query));
					$inventories = $this->fetchShopifyProductInventories($shop, $inventory_level_query);
					Log::channel(channel: 'daily')->info("Inventories data" . json_encode($inventories->getDecodedBody()));
					$inventories = $inventories->getDecodedBody()['inventory_levels'] ?? [];
					SyncShopifyProductsVariantInventories::dispatch(
						$shop,
						$inventories,
						$is_last
					)->onQueue('inventory_location_sync');
				}
			}
		}
		$is_next_page_set = $this->setJobForNextPage($result->getHeader("Link"), $shop);
		if (!$is_next_page_set) {
			$this->finalizeProductSync($shop);
		}
	}
	private function fetchShopifyProducts($shop, $query): bool|HttpResponse
	{
		$result = ShopifyHelper::handleApiException(function () use ($shop, $query) {
			$client = new Rest($shop->shop, $shop->access_token);
			return $client->get('products', [], $query);
		});

		if ($result->getStatusCode() != 200) {
			logger($shop->shop . ' ' . $result->getReasonPhrase());
		}
		return $result;
	}
	public function formatAndCreateVariation($productFromShopify, Session $shop, ShopifyProduct $product, &$item_level_ids, bool $isShopifyInitialSync = false)
	{
		$ids = [];
		$shopifyProductId = $product->__get('id');
		$inventoryJobs = [];
		Log::channel('daily')->info("Variation Products:" . json_encode($productFromShopify['variants']));
		foreach ($productFromShopify['variants'] as $variant) {
			$ids[] = $variant['id'];
			Log::channel('daily')->info("Variation Number:" . json_encode($variant));
			$formattedVariantData = $this->shopifyProductDataService->getFormattedProductVariantDataToStore($variant, $shop);
			$this->shopifyProductRepository->updateOrCreateVariants(
				[
					'session_id' => $shop->id,
					'variant_id' => $variant['id'],
					'shopify_product_id' => $shopifyProductId,
				],
				$formattedVariantData,
				$item_level_ids
			);
		}
		//Confirm here with Dikshant.
		if ($product->wasChanged('updated_at')) {
			$this->shopifyProductRepository->deleteUntouchedVariants($shop->id, $shopifyProductId, $ids);
		}
	}

	private function setJobForNextPage(array $links, $shop)
	{
		if (count($links)) {
			$links = explode(',', $links[0]);
			$next_page = false;
			foreach ($links as $link) {
				$next_page = false;
				if (strpos($link, 'rel="next"')) {
					$next_page = $link;
				}
			}
			if ($next_page) {
				preg_match('~<(.*?)>~', $next_page, $next);
				$url_components = parse_url($next[1]);
				parse_str($url_components['query'], $params);
				$query = array(
					'page_info' => $params['page_info'],
					'limit' => $params['limit'],
				);
				SyncShopifyProducts::dispatch(
					$shop,
					$query,
				)
					->onQueue('shopify_sync');
				return true;
			}
		}
	}

	public function fetchShopifyProductInventories(Session $shop, $query)
	{
		try {
			$client = new Rest($shop->shop, $shop->access_token);
			$result = $client->get('inventory_levels', [], $query);
			if ($result->getStatusCode() != 200) {
				logger($result->getReasonPhrase());
				if ($result->getReasonPhrase() === "Bad Gateway") {
					throw new ShopifyApiRateLimitException('Rate limit exceeded', 10, $result);
				}
			}
			return $result;
		} catch (ShopifyApiRateLimitException $e) {
			//handle exception
			logger("Fetch Product Issue : " . json_encode($e));
			throw new ShopifyApiRateLimitException('Rate limit exceeded', 10, $result);
		} catch (\Exception $e) {
			//handle exception
			logger("Fetch Product Issue : " . json_encode($e));
			return false;
		}
	}

	public function syncShopifyProductInventory(Session $shop, array $inventories, bool $is_last)
	{
		$inventories = collect($inventories);
		$inventories = $inventories->groupBy('inventory_item_id');
		Log::channel('daily')->info('Inventories grouping by id' . json_encode($inventories));
		foreach ($inventories as $inventory_item_id => $inventory_group) {
			$variant = ShopifyProductVariant::where([
				"inventory_item_id" => $inventory_item_id,
				"session_id" => $shop->id
			])->first();
			$api_inventory_data = $inventory_group->pluck('location_id')->toArray();
			$variant_ids = ShopifyVariationInventory::where([
				'session_id' => $shop->id,
				'inventory_item_id' => $inventory_item_id
			])->pluck('location_id')->toArray();
			$deleted_variant_inventory_locations = array_diff($variant_ids, $api_inventory_data);
			foreach ($deleted_variant_inventory_locations as $deleted_variant_inventory_location_id) {
				// Delete from Database
				$this->deleteProductVariantInventory($shop, $variant, $deleted_variant_inventory_location_id);
			}
			foreach ($inventory_group as $inventory) {
				Log::channel('daily')->info("Inventory" . json_encode($inventory));
				Log::channel('daily')->info("Variant" . json_encode($variant));
				$this->createProductVariantInventory($shop, $inventory, $variant);
			}
		}
	}
	public function deleteProductVariantInventory(
		Session $shop,
		$variant,
		$delete_product_variant_inventory_location_id
	) {
		return $this->shopifyProductRepository->deleteProductVariantInventory(
			$shop->id,
			$variant->id,
			$delete_product_variant_inventory_location_id
		);
	}
	public function createProductVariantInventory(Session $shop, $inventory, $variant)
	{
		$inventoryModel = $this->formatAndCreateProductVariantInventory($shop, $inventory, $variant);
		return $inventoryModel;
	}
	private function formatAndCreateProductVariantInventory($shop, $inventory, $variant)
	{
		return $this->shopifyProductRepository->updateOrCreateVariantInventories(
			$shop,
			$inventory,
			$variant
		);
	}
}