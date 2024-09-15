<?php
namespace App\Services\Shopify;

use App\Models\ShopifyLocation;
use Illuminate\Support\Facades\DB;
use Shopify\Clients\Rest;

class ShopifyLocationService
{
    private $client, $session,$store;
    public function __construct($session,$store)
    {
        $this->session = $session; //Session Model
        $this->store = $store;
        $this->client = new Rest($session->shop, $session->access_token);
    }

    public function fetchLocations(){
        $locations = $this->client->get('locations',[],[]);
        $locations_response = json_decode($locations->getBody()->getContents());

        DB::beginTransaction();
        try {
            ShopifyLocation::where('store_id', $this->store->id)->delete();
            foreach($locations_response->locations as $location){
                $location_data = [
                    'store_id' => $this->store->id,
                    'location_id' => $location->id,
                    'name' => $location->name,
                    'address1' => $location->address1,
                    'address2' => $location->address2,
                    'city' => $location->city,
                    'zip' => $location->zip,
                    'province' => $location->province,
                    'country' => $location->country,
                    'phone' => $location->phone,
                    'country_code' => $location->country_code,
                    'country_name' => $location->country_name,
                    'province_code' => $location->province_code,
                    'admin_graphql_api_id' => $location->admin_graphql_api_id,
                    'localized_country_name' => $location->localized_country_name,
                    'localized_province_name' => $location->localized_province_name,
                ];
                ShopifyLocation::create($location_data);
            }

        DB::commit();
        return true;
      }
    catch (\Exception $e) {
        DB::rollBack();
        return false;
    }
    }
}
