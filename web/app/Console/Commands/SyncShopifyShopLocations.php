<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Module\Shopify\Services\Location\SyncShopifyLocationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncShopifyShopLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:fetch_shopify_shop_locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches the shopify shop locations ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(SyncShopifyLocationService $syncShopifyLocationService)
    {
		$shops = Session::whereNotNull('access_token')->get();
		$start_time = time();
		$this->info("Started Syncing Locations");
		foreach($shops as $shop){
			$syncShopifyLocationService->syncShopifyLocations($shop);
		}
		$time_taken = time() - $start_time;
		Log::channel('daily')->info("Shopify Shop Location sync command executed in  $time_taken seconds");
		$this->info("Syncing Location Completed");
    }
}
