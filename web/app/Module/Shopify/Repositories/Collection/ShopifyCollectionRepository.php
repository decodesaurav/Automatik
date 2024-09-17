<?php
namespace App\Module\Shopify\Repositories\Collection;

use App\Models\ShopifyCollection;

use App\Models\Shopify\ShopifyProductsCollection;

use App\Models\ShopifyProduct;

class ShopifyCollectionRepository {
	public function storeCollection ($searchData, $data) {
		return ShopifyCollection::updateOrCreate($searchData, $data);
	}


}