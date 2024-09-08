<?php

namespace App\Services;

use Shopify\Rest\Admin2024_01\Product as ShopifyProduct;
use Shopify\Rest\Admin2024_01\InventoryLevel;
use Shopify\Utils;

class ShopifyProductFetchService
{
	public function fetchProducts()
	{
		$products = [];
		$limit = 250; // Shopify API limit per page
		$page = 1;
		$hasMorePages = true;

		while ($hasMorePages) {
			$response = ShopifyProduct::all(session(), ['page' => $page, 'limit' => $limit]);
			$productsPage = json_decode($response);

			if (empty($productsPage)) {
				$hasMorePages = false;
			} else {
				$products = array_merge($products, $productsPage);
				$page++;
			}
		}

		return $products ?: [];
	}


	public function fetchInventoryLevels($inventoryItemIds)
	{
		$inventoryLevels = [];
		$batchSize = 100; // Define the batch size (adjust this based on API limits)

		// Chunk the inventory item IDs into smaller batches
		$chunks = array_chunk($inventoryItemIds, $batchSize);

		foreach ($chunks as $chunk) {
			// Make an API request for each batch
			$response = InventoryLevel::all(session(), ['inventory_item_ids' => implode(',', $chunk)]);

			// If the response is valid, merge it into the results
			if ($response) {
				$inventoryLevels = array_merge($inventoryLevels, $response);
			}
		}

		return $inventoryLevels;
	}

}
