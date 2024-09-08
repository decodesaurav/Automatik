<?php

namespace App\Repositories;

use App\Models\ProductInventories;

class ShopifyProductInventoryRepository
{
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return ProductInventories::updateOrCreate($attributes, $values);
    }
}
