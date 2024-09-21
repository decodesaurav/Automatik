<?php

namespace App\Jobs;

use App\Exceptions\ShopifyApiRateLimitException;
use App\Models\Session;
use App\Module\Shopify\Services\Collection\SyncShopifyCollectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\Http\Client\ClientExceptionInterface;

class FetchShopifySmartCollection implements ShouldQueue
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
			$syncShopifyCollectionService->syncShopifySmartCollection($this->shop, $this->query);
		} catch (\Exception $e) {
			logger($this->shop->shop . ' FetchShopifySmartCollection ' . $e->getMessage());
		} catch (\JsonException $e) {
			logger($e);
		}
	}

	/**
	 * The unique ID of the job.
	 *
	 * @return string
	 */
	public function uniqueId()
	{
		return "FetchShopifySmartCollection-" . $this->shop->id;
	}
}
