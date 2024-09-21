<?php
namespace App\Module\Shopify\Repositories\Product;
use App\Jobs\FetchShopifyLocations;
use App\Models\Session;
use App\Models\ShopifyProduct;
use App\Models\ShopifyProductVariant;
use App\Models\ShopifyShopLocation;
use App\Models\ShopifyVariationInventory;
use Log;

class ShopifyProductRepository
{
	public function updateOrCreate($query, $data)
	{
		return ShopifyProduct::updateOrCreate($query, $data);
	}

	public function findAndUpdateOrCreateShopifyProduct($query, $data): ShopifyProduct
	{
		$product = ShopifyProduct::where($query)->first();

		if ($product) {
			$product->fill($data);
			$product->save();
		} else {
			$creationData = array_merge($query, $data);
			$product = ShopifyProduct::create($creationData);
		}
		return $product;
	}

	public function updateOrCreateVariants(array $filterData, array $data, &$item_level_ids)
	{
		$variant = ShopifyProductVariant::where($filterData)->first();

		if ($variant) {
			// Now it's safe to set the inventory quantity since the variant exists
			$variant->inventory_quantity = $data['inventory_quantity'];

			Log::channel('daily')->info("Variant was changed condition: " . json_encode($variant->isDirty('inventory_quantity') ? 'true' : 'false'));

			if ($variant->isDirty('inventory_quantity')) {
				$item_level_ids[] = $data['inventory_item_id'];
			}
		} else {
			Log::channel('daily')->info("Else");
			$item_level_ids[] = $data['inventory_item_id'];
		}

		return ShopifyProductVariant::updateOrCreate($filterData, $data);
	}

	public function updateOrCreateImages(array $filterData, array $data)
	{
		return ShopifyProduct::updateOrCreate($filterData, $data);
	}

	public function deleteUntouchedVariants($shop_id, $product_id, array $ids)
	{
		ShopifyProductVariant::where('session_id', $shop_id)
			->where('shopify_product_id', $product_id)
			->whereNotIn('variant_id', $ids)
			->delete();
	}
	public function getStoredShopifyProductsCount($shopify_session_id)
	{
		return ShopifyProduct::where('shopify_session_id', $shopify_session_id)->count();
	}
	public function deleteProductVariantInventory($shop_id, $variant_id, $deleted_variant_inventory_location_id)
	{
		ShopifyVariationInventory::where('session_id', $shop_id)
			->where('variation_id', $variant_id)
			->where('location_id', $deleted_variant_inventory_location_id)
			->delete();
	}
	public function updateOrCreateVariantInventories(
		Session $shop,
		array $inv_data,
		ShopifyProductVariant $variant
	) {
		$shopifyProduct = $variant->shopifyProduct;
		$profile = $shopifyProduct->profile;
		$profile_sync_setting = "not_found";
		if ($profile) {
			$profile_sync_setting = $profile->inventory_sync ? "on" : "off";
		}
		$variantInventory = ShopifyVariationInventory::where([
			"session_id" => $shop->id,
			"inventory_item_id" => $inv_data['inventory_item_id'],
			"shopify_location_id" => $inv_data['location_id'],
		])->first();

		$shopifyLocation = ShopifyShopLocation::where([
			"session_id" => $shop->id,
			"shopify_location_id" => $inv_data['location_id']
		])->first();

		if ($shopifyLocation) {
			if ($variantInventory) {
				$variantInventory->available = $inv_data['available'];
				if ($variantInventory->wasChanged('available') || $variantInventory->wasRecentlyCreated) {
					$variantInventory->update();
				}
			} else {
				$variantInventory = ShopifyVariationInventory::create([
					"session_id" => $shop->id,
					"variation_id" => $variant->id,
					"shopify_location_id" => $inv_data['location_id'],
					"inventory_item_id" => $inv_data['inventory_item_id'],
					"location_id" => $shopifyLocation->id,
					"available" => $inv_data['available'] ?? 0
				]);
			}
		} else {
			FetchShopifyLocations::dispatch($shop);
		}

		return $variantInventory;
	}
}