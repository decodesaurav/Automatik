<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Services\ShopifyProductFetchService;
use App\Repositories\ShopifyProductInventoryRepository;
use App\Repositories\ShopifyProductRepository;
use App\Repositories\ShopifyProductVariantRepository;
use App\DTO\ShopifyProductsDTO;
use App\DTO\ShopifyVariantProductsDTO;
use Illuminate\Support\Facades\Log;

class FetchShopifyProducts extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'shopify:fetch-products';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fetch products, variants, and inventory from Shopify and store in db';

	protected $shopifyService;
	protected $productRepository;
	protected $productVariantRepository;
	protected $productInventoryRepository;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(
		ShopifyProductFetchService $shopifyService,
		ShopifyProductRepository $productRepository,
		ShopifyProductVariantRepository $productVariantRepository,
		ShopifyProductInventoryRepository $productInventoryRepository
	) {
		parent::__construct();
		$this->shopifyService = $shopifyService;
		$this->productRepository = $productRepository;
		$this->productVariantRepository = $productVariantRepository;
		$this->productInventoryRepository = $productInventoryRepository;
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		try {
			$shopifyProducts = $this->fetchShopifyProducts();

			foreach ($shopifyProducts as $productsBatch) {
				$this->processBatch($productsBatch);
			}
		} catch (\Exception $e) {
			$this->logError($e, 'An error occurred during Shopify product fetching.');
		}

		return Command::SUCCESS;
	}

	/**
	 * Fetch products from Shopify.
	 *
	 * @return array
	 */
	protected function fetchShopifyProducts()
	{
		try {
			$batchSize = 100;
			$shopifyProducts = $this->shopifyService->fetchProducts();

			return array_chunk($shopifyProducts, $batchSize);
		} catch (\Exception $e) {
			$this->logError($e, 'Failed to fetch Shopify products.');
			return [];
		}
	}

	/**
	 * Process a batch of Shopify products.
	 *
	 * @param array $productsBatch
	 */
	protected function processBatch(array $productsBatch)
	{
		foreach ($productsBatch as $shopifyProduct) {
			try {
				$this->processProduct($shopifyProduct);
			} catch (\Exception $e) {
				$this->logError($e, 'Error processing product ID: ' . $shopifyProduct->id);
			}
		}
	}

	/**
	 * Process a single Shopify product.
	 *
	 * @param object $shopifyProduct
	 */
	protected function processProduct($shopifyProduct)
	{
		$shopifyProductDTO = new ShopifyProductsDTO($shopifyProduct);

		// Store or update the product.
		$product = $this->productRepository->updateOrCreate(
			['product_id' => $shopifyProduct->id],
			$shopifyProductDTO->toModelAttributes()
		);

		$inventoryItemIds = [];
		foreach ($shopifyProduct->variants as $shopifyVariant) {
			$this->processVariant($shopifyProduct->id, $shopifyVariant);
			$inventoryItemIds[] = $shopifyVariant->inventory_item_id;
		}

		// Process inventory levels after product variants.
		$this->processInventory($inventoryItemIds);
	}

	/**
	 * Process a product variant.
	 *
	 * @param int $productId
	 * @param object $shopifyVariant
	 */
	protected function processVariant($productId, $shopifyVariant)
	{
		$shopifyVariantDTO = new ShopifyVariantProductsDTO($shopifyVariant, $productId);

		// Store or update the variant
		$this->productVariantRepository->updateOrCreate(
			['variant_id' => $shopifyVariant->id],
			$shopifyVariantDTO->toModelAttributes()
		);
	}

	/**
	 * Fetch and process inventory levels for variants.
	 *
	 * @param array $inventoryItemIds
	 */
	protected function processInventory(array $inventoryItemIds)
	{
		try {
			$inventoryLevels = $this->shopifyService->fetchInventoryLevels($inventoryItemIds);

			foreach ($inventoryLevels as $inventory) {
				$this->updateInventoryRecord($inventory);
			}
		} catch (\Exception $e) {
			$this->logError($e, 'Error processing inventory levels.');
		}
	}

	/**
	 * Update or create inventory records.
	 *
	 * @param object $inventory
	 */
	protected function updateInventoryRecord($inventory)
	{
		// Retrieve the corresponding variant by Shopify's inventory_item_id
		$variant = $this->productVariantRepository->getByShopifyVariantId($inventory->inventory_item_id);

		if ($variant) {
			$this->productInventoryRepository->updateOrCreate(
				[
					'inventory_item_id' => $variant->shopify_variant_id,
					'location_id' => $inventory->location_id,
				],
				[
					'inventory_quantity' => $inventory->available,
					'product_id' => $variant->product_id,
					'old_inventory_quantity' => $variant->inventory_quantity,
					'is_tracked' => true,
					'sku' => $variant->sku,
					'updated_at' => Carbon::now(),
				]
			);
		} else {
			// Log the missing variant information for future reference.
			Log::warning('Variant not found for inventory item ID: ' . $inventory->inventory_item_id);
		}
	}

	/**
	 * Log errors with a consistent format.
	 *
	 * @param \Exception $e
	 * @param string $message
	 */
	protected function logError(\Exception $e, $message)
	{
		$this->error($message);
		Log::error($message, [
			'error' => $e->getMessage(),
			'stack' => $e->getTraceAsString(),
		]);
	}
}
