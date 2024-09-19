<?php

namespace App\Module\Shopify\Services\Location;

use App\Jobs\FetchShopifyLocations;
use App\Models\Session;
use App\Module\Shopify\Helper\ShopifyHelper;
use App\Module\Shopify\Repositories\Location\ShopifyLocationRepository;
use Illuminate\Support\Facades\Log;
use Shopify\Clients\Graphql;

class SyncShopifyLocationService
{
	private const FETCH_LOCATION_QUERY = array(
		"query" => '
		query {
			locations(first: 15, sortKey:ID, includeLegacy: true) {
			edges {
				node {
				id
				name
				hasActiveInventory
				fulfillsOnlineOrders
				isPrimary
				shipsInventory
				isActive
				address {
					address1
					address2
					city
					zip
					province
					country
					countryCode
					phone
				}
				}
			}
			}
		}'
	);

	public function __construct(protected ShopifyLocationRepository $shopifyLocationRepository)
	{
	}

	public function syncShopifyLocations($shop)
	{
		FetchShopifyLocations::dispatch($shop)->onQueue('sync_shopify_locations');
	}

	public function setJob($shop)
	{
		Log::channel('daily')->info("Session Id arrived" . json_encode($shop));
		$query = self::FETCH_LOCATION_QUERY;
		$client = new Graphql($shop->shop, $shop->access_token);
		$response = $client->query($query);
		if ($response->getStatusCode() !== 200) {
			Log::channel("daily")->critical("ShopifyLocationFetchFailed Error occurred for shop $shop->id shopify location fetch not 200 reason " . json_encode($response->getDecodedBody()));
			return;
		}
		$locationDatum = json_decode($response->getBody()->__toString());

		if (!isset($locationDatum->data->locations->edges)) {
			return;
		}
		$activeLocations = [];
		$locations = $locationDatum->data->locations->edges;

		foreach ($locations as $location) {
			$location = $location->node;

			$locationId = ShopifyHelper::decodeGraphqlID($location->id);
			$activeLocations[] = $locationId;
			$formattedData = $this->getFormattedDataToStore($location);

			$this->shopifyLocationRepository->updateOrCreate(
				[
					'shopify_location_id' => $locationId,
					'session_id' => $shop->id,
				],
				$formattedData
			);
		}
		$this->removeInactiveLocations($activeLocations, $shop->id);
	}

	public function removeInactiveLocations($activeLocations, $sessionId)
	{
		$this->shopifyLocationRepository->removeInactiveLocation($sessionId,$activeLocations);
	}

	public function getFormattedDataToStore($location)
	{
		return [
			'name' => $location->name,
			'address1' => $location->address->address1,
			'address2' => $location->address->address2,
			'city' => $location->address->city,
			'province' => $location->address->province,
			'country' => $location->address->country,
			'zip' => $location->address->zip,
			'phone' => $location->address->phone,
			'is_active' => $location->isActive ? 1 : 0,
			'is_primary' => $location->isPrimary ? 1 : 0,
		];
	}
}