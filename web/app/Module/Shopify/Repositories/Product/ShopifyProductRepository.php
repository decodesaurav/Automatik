<?php
namespace App\Module\Shopify\Repositories\Product;
use App\Models\ShopifyProduct;
use App\Models\ShopifyProductVariant;

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

	public function updateOrCreateVariants(array $filterData, array $data){
		return ShopifyProductVariant::updateOrCreate($filterData, $data);
	}
	public function updateOrCreateImages(array $filterData, array $data){
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
}