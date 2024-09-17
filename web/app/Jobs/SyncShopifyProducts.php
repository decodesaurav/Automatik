<?php

namespace App\Jobs;

use App\Exceptions\ShopifyApiRateLimitException;
use App\Models\Session;
use App\Module\Shopify\Services\Product\SyncShopifyProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncShopifyProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public int $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected Session $shop, protected array $query)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SyncShopifyProductService $service)
    {
		$isShopifyInitialSync = !$this->shop->shopify_last_import_at;
		try{
			$service->syncShopifyProduct(
				$this->shop,
				$this->query,
				$isShopifyInitialSync
			);
		} catch (ShopifyApiRateLimitException $e) {
            logger($this->shop->shop . 'Jobs:SyncShopifyProducts Restarting after API rate limit. ');
            $this->release($e->getRetryAfter());
        } catch (\JsonException $e) {
            logger("Jobs:SyncShopifyProducts Json exception on syncShopify products: " . $e->getMessage());
        } catch (\Exception $e) {
            logger($this->shop->shop . "Jobs:SyncShopifyProducts exception message: " . $e->getMessage());
        }
    }
}
