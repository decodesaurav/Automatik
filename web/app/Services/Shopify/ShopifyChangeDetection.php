<?php

namespace App\Services\Shopify;

use App\Jobs\FetchShopifyChangeJob;
use App\Models\Session;
use Shopify\Clients\Rest;

class ShopifyChangeDetection{
    public $client, $params, $store_id, $session, $store;
    public function __construct($store,$params=[])
    {
        $this->store=$store;
        $this->store_id = $store->id;
        $this->session = Session::where('id',$store->id)->first();
        $this->client = new Rest($store->domain, $store->token);
        $this->params = $params;
    }

    public function startDetection(){
        //Shopify Custom Collection Change Detection
        $this->detectCustomCollectionChange();

        //Shopify Digital Collection Change Detection
        $this->detectSmartCollectionChange();

        //Shopify Custom Product Change Detection
        $this->detectProductsChange();

        if(!isset($this->params['updated_at_min'])){
            $this->fetchShopifyLocations();
        }

        // TODO: Shopify Location Change Detection
    }

    public function detectCustomCollectionChange(){
        $products = $this->client->get('custom_collections/count',[],$this->params);
        $product_response = json_decode($products->getBody()->getContents());

        if(isset($product_response->count) && $product_response->count > 0){
            $this->dispatchJob('custom_collection_fetch',$product_response->count);
        }
    }

    public function detectSmartCollectionChange(){
        $products = $this->client->get('smart_collections/count',[],$this->params);
        $product_response = json_decode($products->getBody()->getContents());

        if(isset($product_response->count) && $product_response->count > 0){
            $this->dispatchJob('smart_collection_fetch',$product_response->count);
        }
    }

    public function detectProductsChange(){
        $products = $this->client->get('products/count',[],$this->params);
        $product_response = json_decode($products->getBody()->getContents());

        if(isset($product_response->count) && $product_response->count > 0){
            $this->dispatchJob('product_fetch',$product_response->count);
        }
    }

    public function fetchShopifyLocations(){
        $shopifyLocationService = new ShopifyLocationService($this->session,$this->store);
        $shopifyLocationService->fetchLocations();
        return true;
    }

    protected function dispatchJob($job_type, $changes_count=0){
        dispatch(new FetchShopifyChangeJob($this->store_id,$job_type,$changes_count));
    }

}