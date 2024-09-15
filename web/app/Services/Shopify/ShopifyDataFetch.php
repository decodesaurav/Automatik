<?php

namespace App\Services\Shopify;

use App\Jobs\FetchShopifyChangeJob;
use App\Models\Session;
use App\Models\ShopifyCollection;
use App\Models\ShopifyProducts;
use App\Models\ShopifyProductsVariant;
use App\Models\ShopifyShop;
use App\Services\Shopify\ShopifyLocationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Shopify\Clients\Rest;

class ShopifyDataFetch
{
	protected $store, $store_id, $changed_data, $change_count, $client, $params, $store_last_synced, $session;

	public function __construct($store_id)
	{
		$this->store_id = $store_id;
		$this->store = ShopifyShop::where('id', $store_id)->first();
		$this->client = new Rest($this->store->domain, $this->store->token);
		$this->session = Session::where('id', $this->store->session_id)->first();
		$this->params = [];
		$this->store_last_synced = null;
		if (!is_null($this->store->last_synced_at)) {
			$carbonUtc = Carbon::parse($this->store->last_synced_at, 'UTC');
			$formattedDateTime = $carbonUtc->format('Y-m-d\TH:i:sP');
			$this->params['updated_at_min'] = $formattedDateTime;
			$this->store_last_synced = $formattedDateTime;
		}
	}

	public function fetchProducts()
	{
		$this->params['status'] = 'active';
		$this->params['limit'] = 100;

		$products_count = $this->client->get('products/count', [], $this->params);
		$products_count_response = json_decode($products_count->getBody()->getContents());
		if (isset($products_count_response->count) && $products_count_response->count > 0) {
			$this->processShopifyProducts($this->params, $products_count_response->count);
		}

		//After Fetching Product Update Last Synced!!
		$this->store->update([
			'last_synced_at' => now()->format('Y-m-d H:i:s')
		]);
	}

	public function fetchLocations()
	{
		$shopifyLocationService = new ShopifyLocationService($this->session, $this->store);
		$shopifyLocationService->fetchLocations();
		return;
	}

	public function fetchCustomCollections()
	{
		$collections = $this->client->get('custom_collections', [], $this->params);
		$collection_response = json_decode($collections->getBody()->getContents());
		if (isset($collection_response)) {
			foreach ($collection_response->custom_collections as $singleCollection) {
				$this->processCollection($singleCollection, 'custom_collection');
			}
		}
	}

	public function fetchSmartCollections()
	{
		$collections = $this->client->get('smart_collections', [], $this->params);
		$collection_response = json_decode($collections->getBody()->getContents());
		if (isset($collection_response)) {
			foreach ($collection_response->smart_collections as $singleCollection) {
				$this->processCollection($singleCollection, 'smart_collection');
			}
		}
	}

	public function processCollection($singleCollection, $collectionType)
	{
		$collection = $this->client->get('collections/' . $singleCollection->id, [], $this->params);
		$collection_response = json_decode($collection->getBody()->getContents());

		//Save or Update Collection
		if (isset($collection_response->collection)) {
			$collection = $collection_response->collection;

			$local_collection = ShopifyCollection::where([
				'store_id' => $this->store_id,
				'shopify_collection_id' => $collection->id
			])->first();

			if ($local_collection) {
				//update
				$local_collection->update([
					'title' => $collection->title,
					'handle' => $collection->handle,
					'body_html' => $collection->body_html,
					'collection_type' => $collection->collection_type,
					'published_scope' => $collection->published_scope,
					'products_count' => $collection->products_count,
				]);
				Log::channel('daily')->info("Collection Updated");
			} else {
				//Create
				ShopifyCollection::create([
					'store_id' => $this->store_id,
					'shopify_collection_id' => $collection->id,
					'title' => $collection->title,
					'handle' => $collection->handle,
					'body_html' => $collection->body_html,
					'collection_type' => $collection->collection_type,
					// 'image_data' => $collection->image_data,
					'published_scope' => $collection->published_scope,
					'products_count' => $collection->products_count,
				]);
				Log::channel('daily')->info("Collection Created");
			}

			$collection_params = [
				'collection_id' => $collection->id,
				'status' => 'active',
				'updated_at_min' => $this->store_last_synced
			];
			if (is_null($this->store_last_synced)) {
				unset($collection_params['updated_at_min']);
			}

			$collection_products = $this->client->get('products/count', [], $collection_params);
			$collection_products_response = json_decode($collection_products->getBody()->getContents());

			if (isset($collection_products_response->count) && $collection_products_response->count > 0) { {
					$this->processShopifyProducts($collection_params, $collection_products_response->count, $collection->id);
				}
			}
			$this->setCustomCollectionJobForNextPage(
				$collection->getHeader("Link"),
				$this->store_id,
				$this->params,
				$collectionType
			);
		}
	}

	public function setCustomCollectionJobForNextPage(array $links, $shop, $query, $jobType): bool
	{
		if ($next_page_info = $this->getNextPageInfo($links)) {
			$query['page_info'] = $next_page_info;
			$query['limit'] = 1;
			dispatch(new FetchShopifyChangeJob($shop, $jobType, $count = 0));
			return true;
		}
		return false;
	}

	public function getNextPageInfo(array $link)
	{
		if (count($link)) {
			$links = explode(',', $link[0]);
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
				return $params['page_info'];
			}
		}
		return false;
	}
	public function processShopifyProducts($params, $count, $collection_id = null, $returnIndex = null)
	{
		try {
			$response = $this->client->get('products', [], $params);
			$response_body = json_decode($response->getBody()->getContents());

			if (isset($response_body->products)) {
				$products = $response_body->products;
				Log::channel('daily')->info("Product count: " . count($products));
				foreach ($products as $index => $product) {
					$productData = [
						'store_id' => $this->store_id,
						'shopify_product_id' => $product->id,
						'title' => $product->title,
						'handle' => $product->handle,
						'body_html' => $product->body_html,
						'vendor' => $product->vendor,
						'product_type' => $product->product_type,
						'shopify_created_at' => $product->created_at,
						'shopify_updated_at' => $product->updated_at,
						'published_at' => $product->published_at,
						'published_scope' => $product->published_scope,
						'status' => $product->status,
						'tags' => $product->tags,
						'variants' => json_encode($product->variants),
						'options' => json_encode($product->options),
						'images' => json_encode($product->images),
						'image' => json_encode($product->image),
					];

					if (!is_null($collection_id)) {
						$productData['collection_id'] = $collection_id;
					}
					Log::channel('daily')->info("Product Being updated/saved: " . $product->id);
					$updateCondition = [
						'store_id' => $this->store_id,
						'shopify_product_id' => $product->id
					];

					$productItem = ShopifyProducts::updateOrCreate($updateCondition, $productData);
					$product_id = $productItem->id;
					//Handle Variation Sync Case
					if (true) {//If Metafield Sync Enabled -> then only
						$this->processProductMetafields($product->id, $productItem);
					}
					$this->processVariationData($product->variants, $product->id);
				}
				$this->setJobForNextPage(
					$response->getHeader("Link"),
					$count
				);
			}
		} catch (Exception $e) {
			Log::channel('daily')->info("Product Saving Failed for Store" . $this->store_id . " Message:" . $e->getMessage());
		}
	}

	private function setJobForNextPage(array $link, $count)
	{
		if (count($link)) {
			$links = explode(',', $link[0]);
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
				$this->params['page_info'] = $params['page_info'];
				$this->params['limit'] = $params['limit'];
				FetchShopifyChangeJob::dispatch($this->store_id, 'product_fetch', $count);
			}
		}
	}

	public function processVariationData($variationData, $shopify_product_id)
	{
		try {
			foreach ($variationData as $singleVariation) {
				$variationPrepatedData = [
					'store_id' => $this->store_id,
					'shopify_product_id' => $shopify_product_id,
					'shopify_variant_id' => $singleVariation->id,
					'title' => $singleVariation->title,
					'price' => $singleVariation->price,
					'sku' => $singleVariation->sku,
					'position' => $singleVariation->position,
					'inventory_policy' => $singleVariation->inventory_policy,
					'image_id' => $singleVariation->image_id,
					'weight' => $singleVariation->weight,
					'weight_unit' => $singleVariation->weight_unit,
					'inventory_quantity' => $singleVariation->inventory_quantity,
					'old_inventory_quantity' => $singleVariation->old_inventory_quantity,
					'requires_shipping' => $singleVariation->requires_shipping,
					'option1' => $singleVariation->option1,
					'option2' => $singleVariation->option2,
					'option3' => $singleVariation->option3,
					'grams' => $singleVariation->grams,
					'taxable' => $singleVariation->taxable,
					'fulfillment_service' => $singleVariation->fulfillment_service,
					'inventory_management' => $singleVariation->inventory_management,
				];

				$updateCondition = [
					'shopify_variant_id' => $singleVariation->id,
					'store_id' => $this->store_id,
					'shopify_product_id' => $shopify_product_id
				];

				// Create or update the Variation
				ShopifyProductsVariant::updateOrCreate($updateCondition, $variationPrepatedData);
			}
		} catch (Exception $e) {
			Log::channel('daily')->info("Variation Saving Failed for Store" . $this->store_id . " Message:" . $e->getMessage());
		}
	}

	public function processProductMetafields($shopify_product_id, $productItem)
	{
		try {
			$metafields_query = $this->client->get('products/' . $shopify_product_id . '/metafields', []);
			$metafields_data = json_decode($metafields_query->getBody()->getContents());
			$metafields = $metafields_data->metafields;

			$metafield_data = [];
			foreach ($metafields as $singleMetafield) {
				$metafield_data[$singleMetafield->key] = $singleMetafield->value;
			}

			$productItem->update([
				'metafields' => $metafield_data
			]);
		} catch (Exception $e) {
			Log::channel('daily')->info("Metafields Saving Failed for Store" . $this->store_id . " Message:" . $e->getMessage());
		}
	}

}