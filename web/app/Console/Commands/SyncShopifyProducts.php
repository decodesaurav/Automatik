<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Module\Shopify\Services\Collection\SyncShopifyCollectionService;
use App\Module\Shopify\Services\Product\SyncShopifyProductService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncShopifyProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:shopify-products {page?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync shopify product to Automatik';

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
    public function handle(SyncShopifyProductService $syncShopifyProductService, SyncShopifyCollectionService $syncShopifyCollectionService)
    {
		$page = (int) $this->argument('page');
		$shops = Session::whereNotNull('access_token');

		if($page){
			$shops = $shops->skip(500*($page-1))->take(500);
		}
		$shops = $shops->get();
		$start_time = time();
		foreach($shops as $shop){
			$this->info("Started Syncing Products");
			$now = Carbon::parse(now())->toISOString();
			$syncShopifyProductService->setJob($shop);
			$shop->update([
				'shopify_last_import_at' => $now
			]);
		}
		$time_taken = time() - $start_time;
		Log::channel('daily')->info("Shopify product sync command executed in  $time_taken seconds");
		$this->info("Product Syncing completed");
    }
}
