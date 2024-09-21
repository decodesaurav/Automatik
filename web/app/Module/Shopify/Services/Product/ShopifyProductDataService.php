<?php 
namespace App\Module\Shopify\Services\Product;
use App\Models\Session;

class ShopifyProductDataService{

	public function getFormattedProductDataToStore($productFromShopify, string $shopName){
        $data = [
            'descriptionHtml' => $productFromShopify['body_html'],
            'handle' => $productFromShopify['handle'],
            'title' => $productFromShopify['title'],
            'status' => $productFromShopify['status'],
            'tags' => $productFromShopify['tags'],
            'product_type' => $productFromShopify['product_type'],
            'vendor' => $productFromShopify['vendor'],
            'isVariable' => true,
            'shopify_product_url' => sprintf("https://%s/admin/products/%s", $shopName, $productFromShopify['id']),
            'is_active' => true,
        ];
		$variantsCount = count($productFromShopify['variants']);
        if (
            $variantsCount === 1
            && $productFromShopify['options'][0]['values'] === ["Default Title"]
        ) {
            $data['isVariable'] = false;
        }

        if ($productFromShopify['image']) {
            $data['image_src'] = $productFromShopify['image']['src'];
        }
        return $data;
	}

	public function getFormattedProductVariantDataToStore($variant, Session $session){
		return [
            'title' => $variant['title'],
            'price' => $variant['price'],
            'sku' => $this->handleShopifyVariantSkuGeneration($session, $variant['id'], $variant['sku'] ?? ''),
            'inventory_policy' => $variant['inventory_policy'],
            'option1' => $variant['option1'],
            'option2' => $variant['option2'],
            'option3' => $variant['option3'],
            'image_id' => $variant['image_id'],
            'inventory_quantity' => $variant['inventory_quantity'],
            'inventory_item_id' => $variant['inventory_item_id'],
            'weight' => $variant['weight'],
            'weight_unit' => $variant['weight_unit'],
            'barcode' => $variant['barcode'],
            'is_tracked' => !is_null($variant['inventory_management']),
        ];
	}
	private function handleShopifyVariantSkuGeneration(
        Session $session,
        $variant_id,
        string $sku
    ): string {
        if (empty($sku) && $session->__get('auto_sku_generation')) {
            $sku = "ATMK-{$variant_id}";
        }
        return $sku;
    }
}