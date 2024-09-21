<?php

namespace App\Jobs;

use App\Models\Session;
use App\Module\Shopify\Services\Product\SyncShopifyProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncShopifyProductsVariantInventories implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected Session $shop;
	protected array $inventories;
	protected bool $is_last;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(Session $shop, array $inventories, bool $is_last)
	{
		$this->shop = $shop;
		$this->inventories = $inventories;
		$this->is_last = $is_last;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(SyncShopifyProductService $service)
	{
		$service->syncShopifyProductInventory($this->shop, $this->inventories, $this->is_last);
	}

	public function uniqueId()
	{
		return "FetchShopifyLocationWiseInventory-" . $this->shop->id;
	}
}
