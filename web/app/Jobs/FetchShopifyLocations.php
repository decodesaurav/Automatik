<?php

namespace App\Jobs;

use App\Exceptions\ShopifyApiRateLimitException;
use App\Exceptions\ShopifyClosedStoreException;
use App\Exceptions\ShopifyUninstalledStoreException;
use App\Models\Session;
use App\Module\Shopify\Services\Location\SyncShopifyLocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchShopifyLocations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected Session $shop)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SyncShopifyLocationService $fetchShopifyLocationService)
    {
		try{
			$fetchShopifyLocationService->setJob($this->shop);
		}catch (ShopifyApiRateLimitException $e) {
			logger($this->shop->shop . ' FetchShopifyShopLocation ' . $e->getMessage());
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
		return "FetchShopifyShopLocations-" . $this->shop->id;
	}
}
