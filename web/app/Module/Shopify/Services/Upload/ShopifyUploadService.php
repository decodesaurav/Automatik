<?php
namespace App\Module\Shopify\Services\Upload;

use App\Models\ShopifyProduct;
use App\Models\ShopifyProductVariant;
use App\Models\ShopifyVariationInventory;
use App\Module\Shopify\Helper\ShopifyHelper;
use Shopify\Clients\Rest;

class ShopifyUploadService
{
	public function __construct()
	{

	}

	public function handleShopifyUpload($shopifyProductRow, $variantData, $shop, $query)
	{
		$handleShopifyUpdateService = $this->uploadProductToShopify($shopifyProductRow, $variantData, $shop);
	}

	public function uploadProductToShopify($shopifyProductRow, $variantData, $shop)
	{
		$preparedDataToUpload = $this->prepareDataToUpload($shopifyProductRow, $variantData);
		$productId = $shopifyProductRow->shopify_product_id;
		$result = ShopifyHelper::handleApiException(function () use ($shop, $productId, $preparedDataToUpload) {
			$client = new Rest($shop->shop, $shop->access_token);
			return $client->put("products/$productId", $preparedDataToUpload);
		});
		return $result;
	}

	public function prepareDataToUpload($data, $variantData)
	{
		$productData = [
			'title' => $data['title'] ?? '',
			'body_html' => $data['descriptionHtml'] ?? '',
			'status' => $data['status'] ?? '',
			'tags' => $data['tags'] ?? '',
			'vendor' => $data['vendor'] ?? ''
		];
		$variants = [];

		foreach ($variants as $variant) {
			$variants[] = [
				'title' => $variant['title'] ?? '',
				'price' => $variant['price'] ?? '',
				'sku' => $variant['sku'] ?? '',
				'inventory_quantity' => $variant['inventory_quantity'] ?? '',
				'option1' => $variant['option1'] ?? '',
				'option2' => $variant['option2'] ?? '',
				'option3' => $variant['option3'] ?? '',
				'inventory_item_id' => $variant['inventory_item_id'] ?? '',
			];
		}

		if (!empty($variants)) {
			$productData['variants'] = $variants;
		}

		$returnData['product'] = $productData;
		return $returnData;
	}
}