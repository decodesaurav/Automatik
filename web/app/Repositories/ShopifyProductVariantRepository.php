<?php

namespace App\Repositories;

use App\Models\ShopifyProductsVariant;

class ShopifyProductVariantRepository
{
	public function updateOrCreate(array $attributes, array $values = [])
	{
		return ShopifyProductsVariant::updateOrCreate($attributes, $values);
	}
	// Retrieve a product variant by the Shopify variant (inventory item) ID
	public function getByShopifyVariantId($shopifyVariantId)
	{
		$variant = ShopifyProductsVariant::where('shopify_variant_id', $shopifyVariantId)->first();
		if (!$variant) {
			\Log::warning("Variant not found for Shopify Variant ID: " . $shopifyVariantId);
		}
		return $variant;
	}


}
