<?php

namespace App\Module\Shopify\Services\Collection;

use App\Exceptions\ShopifyApiRateLimitException;
use App\Exceptions\ShopifyClosedStoreException;
use App\Exceptions\ShopifyUninstalledStoreException;
use App\Jobs\Shopify\FetchShopifyCollectionProducts;
use App\Jobs\FetchShopifyCustomCollection;
use App\Jobs\FetchShopifySmartCollection;
use App\Models\Session;
use App\Models\Shopify\ShopifyCollection;
use App\Models\ShopifyProduct;
use App\Module\Shopify\Helper\ShopifyHelper;
use App\Module\Shopify\Repositories\Collection\ShopifyCollectionRepository;
use App\Module\Shopify\Trait\APIHelper;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Psr\Http\Client\ClientExceptionInterface;
use Shopify\Clients\HttpResponse;
use Shopify\Clients\Rest;
use Shopify\Exception\MissingArgumentException;
use Shopify\Exception\UninitializedContextException;

class SyncShopifyCollectionService
{
	use APIHelper;

	public function __construct(protected ShopifyCollectionRepository $shopifyCollectionRepository)
	{

	}

	public function setJob(Session $shop, $onQueue = 'shopify_collection_sync')
	{
		$total_shopify_products_count = $this->fetchShopifyCustomCollectionCount($shop);
		$query = [];
		$query['limit'] = 1;
		if ($shop->last_collection_import_at) {
			$date = new DateTime($shop->last_collection_import_at, new DateTimeZone('UTC'));
			$query['updated_at_min'] = $date->format('c');
		}
		if ($total_shopify_products_count) {
			FetchShopifyCustomCollection::dispatch($shop, $query)->onQueue($onQueue);
		}
		$total_smart_collection_count = $this->fetchShopifySmartCollectionCount($shop, $query['updated_at_min'] ?? '');
		if ($total_smart_collection_count) {
			if (isset($query['page_info'])) {
				unset($query['page_info']);
			}
			FetchShopifySmartCollection::dispatch($shop, $query)->onQueue('shopify_collection_sync');
		}
	}

	public function fetchShopifyCustomCollectionCount($shop)
	{
		$query = [];
		if ($shop->last_collection_import_at) {
			$query['updated_at_min'] = $shop->last_collection_import_at;
		}
		$client = new Rest($shop->shop, $shop->access_token);
		$result = $client->get('custom_collections/count', [], $query);

		$result = $result->getDecodedBody();

		if (array_key_exists('count', $result)) {
			return $result['count'];
		} else {
			return 0;
		}
	}
	public function fetchShopifySmartCollectionCount(Session $shop, string $updated_at_min)
	{
		$query = [];
		if ($updated_at_min) {
			$query['updated_at_min'] = $updated_at_min;
		}
		$client = new Rest($shop->shop, $shop->access_token);
		$result = $client->get('smart_collections/count', [], $query);
		$result = $result->getDecodedBody();
		if (array_key_exists('count', $result)) {
			return $result['count'];
		} else {
			return 0;
		}
	}

	public function syncShopifyCustomCollection($shop, $query)
	{
		$start_time = time();
		logger("$shop->shop started custom collection sync with query:" . json_encode($query));
		$result = $this->fetchShopifyCustomCollection($shop, $query);
		if (!$result) {
			logger("$shop->shop No result found, fetching smart collection with query " . json_encode($query));
			$this->setSmartCollectionJob($shop, $query);
			return;
		}

		if (!array_key_exists('custom_collections', $result->getDecodedBody())) {
			logger(
				"Custom collection job issue on shop:" .
				json_encode($shop) . json_encode($query) .
				". Response: " . json_encode($result->getDecodedBody())
			);
			$this->setSmartCollectionJob($shop, $query);
			return;
		}
		$collections = $result->getDecodedBody()['custom_collections'];
		$this->postActionForShopifyCollection($collections, $shop);
		$next_page = $this->setCustomCollectionJobForNextPage($result->getHeader("Link"), $shop, $query);
		if (!$next_page) {
			$this->setSmartCollectionJob($shop, $query);
		}
		$time_taken = time() - $start_time;

		log::channel('daily')->info("$shop->shop completed syncShopifyCollection in $time_taken seconds");
	}
	private function fetchShopifyCustomCollection($shop, $query): bool|HttpResponse
	{
		if (array_key_exists('page_info', $query)) {
			unset($query['updated_at_min']);
		}

		$result = ShopifyHelper::handleApiException(function () use ($shop, $query) {
			$client = new Rest($shop->shop, $shop->access_token);
			return $client->get('custom_collections', [], $query);
		});

		if ($result->getStatusCode() != 200) {
			if (in_array($result->getStatusCode(), [404, 402])) {
				throw new ShopifyClosedStoreException("Disabling sync for closed store.", $result);
			}
			if ($result->getStatusCode() == 401) {
				throw new ShopifyUninstalledStoreException("Removing uninstalled store.", $result);
			}
			logger($result->getReasonPhrase());
		}
		return $result;
	}

	public function setSmartCollectionJob($shop, array $query)
	{
		$total_shopify_products_count = $this->fetchShopifySmartCollectionCount($shop, $query['updated_at_min'] ?? '');
		if (!$total_shopify_products_count) {
			return;
		}
		if (isset($query['page_info'])) {
			unset($query['page_info']);
		}
		FetchShopifySmartCollection::dispatch($shop, $query)->onQueue('shopify_collection_sync');
	}

	public function postActionForShopifyCollection(mixed $collections, $shop): void
	{
		foreach ($collections as $collection) {
			$collectionObject = $this->shopifyCollectionRepository->storeCollection(
				[
					'session_id' => $shop->id,
					'shopify_session_id' => $shop->session_id,
					'shopify_collection_id' => $collection['id']
				],
				[
					'handle' => $collection['handle'],
					'title' => $collection['title'],
					'updated_at_shopify' => $collection['updated_at'],
					'image_src' => isset($collection['image']) ? $collection['image']['src'] : ''
				]
			);
		}
	}

	public function setCustomCollectionJobForNextPage(array $links, $shop, $query ){
		if($next_page_info = $this->getNextPageInfo($links)) {
			$query['page_info'] = $next_page_info;
			FetchShopifyCustomCollection::dispatch($shop, $query)->onQueue('shopify_collection_sync');
			return true;
		}
		return false;
	}

	public function syncShopifySmartCollection($shop, $query){
		$start_time = strtotime("now");
		logger("$shop->shop started smart collection sync with query:" . json_encode($query));

		$result = $this->fetchShopifySmartCollection($shop, $query);
		if(!$result){
			logger("$shop->shop No result found while fetching the smart collection with query" . json_encode($query));
			return;
		}
		if(!array_key_exists('smart_collections', $result->getDecodedBody())){
            logger(
                "Smart collection job issue on shop:"
                . json_encode($shop) . json_encode($query) .
                ". Response: " . json_encode($result->getDecodedBody())
            );
			return;
		}
		$collections = $result->getDecodedBody()['smart_collections'];
        $this->postActionForShopifyCollection($collections, $shop);
        $this->setSmartCollectionJobForNextPage(
            $result->getHeader("Link"),
            $shop
        );
		$time_taken = strtotime("now") - $start_time;
        logger("$shop->shop Finalized syncShopifySmartCollection in $time_taken seconds");
	}

	private function fetchShopifySmartCollection($shop,$query){
		$result = ShopifyHelper::handleApiException(function() use ($shop,$query){
			$client = new Rest($shop->shop, $shop->access_token);
			return $client->get('smart_collections', [], $query);
		});

		if($result->getStatusCode() !== 200){
			if(in_array($result->getStatusCode(), [404,402])){
				throw new ShopifyClosedStoreException("Disable the function for the closed store" . $result);
			}
			if($result->getStatusCode() == 401){
				throw new ShopifyUninstalledStoreException("Removing Uninstalled Store", $result);
			}
			logger($result->getReasonPhrase());
		}
		return $result;
	}

	public function setSmartCollectionJobForNextPage($links, $shop){
		if($next_page_info = $this->getNextPageInfo($links)){
			$query['page_info'] = $next_page_info;
			$query['limit']=1;
			FetchShopifySmartCollection::dispatch($shop,$query)->onQueue('shopify_collection_sync');
			return true;
		}
		return false;
	}
}