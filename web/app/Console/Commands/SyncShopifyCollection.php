<?php

namespace App\Console\Commands;
use App\Models\Session;
use App\Module\Shopify\Services\Collection\SyncShopifyCollectionService;


use Illuminate\Console\Command;

class SyncShopifyCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:shopify-collection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle(SyncShopifyCollectionService $syncShopifyCollectionService)
    {
        $shops = Session::whereNotNull('access_token')->get();
		$this->info("Started Syncing Collection");
		foreach($shops as $shop) {
			$syncShopifyCollectionService->setJob($shop);
			logger("Setting Collection Sync Job for $shop->shop");
		}
		$this->info("Collection Sync completed");
    }
}
