<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Exceptions\ShopifyApiRateLimitException;
use App\Exceptions\ShopifyClosedStoreException;
use App\Exceptions\ShopifyUninstalledStoreException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Module\Shopify\Services\Collection\SyncShopifyCollectionService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Session;

class FetchShopifyCustomCollection implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(protected Session $shop, protected array $query)
	{
		//
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(SyncShopifyCollectionService $syncShopifyCollectionService)
	{
		try {
			$syncShopifyCollectionService->syncShopifyCustomCollection($this->shop, $this->query);
		} catch (ShopifyApiRateLimitException $e) {
			logger($this->shop->shop . ' FetchShopifyCustomCollection ' . $e->getMessage());
			$this->release($e->getRetryAfter());
		} catch (\JsonException $e) {
			logger($e);
		} catch (ShopifyClosedStoreException $e) {
			$this->shop->update(['enable_sync' => false]);
		} catch (ShopifyUninstalledStoreException $e) {
			$this->shop->delete();
		}
	}
	public function uniqueId()
	{
		return "FetchShopifyCustomCollection-" . $this->shop->id;
	}
}
