<?php

namespace App\Jobs;

use App\Services\Shopify\ShopifyDataFetch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchShopifyChangeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $store_id, $changed_data, $change_count, $shopifyDataFetcher;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_id,$changed_data,$change_count=0)
    {
        $this->store_id = $store_id;
        $this->changed_data = $changed_data;
        $this->change_count = $change_count;
        $this->shopifyDataFetcher = new ShopifyDataFetch($this->store_id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		switch ($this->changed_data) {
            case "product_fetch":
                Log::channel('daily')->info("Job dispatched ". $this->changed_data . ' Changed Count: '. $this->change_count);
                $this->shopifyDataFetcher->fetchProducts();
                break;
            case "custom_collection_fetch":
                Log::channel('daily')->info("Job dispatched ". $this->changed_data . ' Changed Count: '. $this->change_count);
                $this->shopifyDataFetcher->fetchCustomCollections();
                break;
            case "smart_collection_fetch":
                Log::channel('daily')->info("Job dispatched ". $this->changed_data . ' Changed Count: '. $this->change_count);
                $this->shopifyDataFetcher->fetchSmartCollections();
                break;
            case "location_fetch":
                $this->shopifyDataFetcher->fetchLocations();
                Log::channel('daily')->info("Job dispatched ". $this->changed_data . ' Changed Count: '. $this->change_count);
                break;
            default:
                // Code to be executed if none of the cases match
        }
    }
}
