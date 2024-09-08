<?php
namespace App\Repositories;

use App\Models\ShopifyProducts;

class ShopifyProductRepository
{
	public function updateOrCreate(array $attributes, array $values = [])
	{
		return ShopifyProducts::updateOrCreate($attributes, $values);
	}
}