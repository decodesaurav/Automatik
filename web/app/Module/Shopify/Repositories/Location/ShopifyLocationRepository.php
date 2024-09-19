<?php 
namespace App\Module\Shopify\Repositories\Location;

use App\Models\ShopifyShopLocation;

class ShopifyLocationRepository {
	public function updateOrCreate($query, $data){
		return ShopifyShopLocation::updateOrCreate($query, $data);
	}

	public function removeInactiveLocation($sessionId, $activeLocations){
		ShopifyShopLocation::where('session_id', $sessionId)
		->whereNotIn('shopify_location_id', $activeLocations)
		->delete();
	}
}