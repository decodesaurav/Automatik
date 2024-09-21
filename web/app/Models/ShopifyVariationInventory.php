<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyVariationInventory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function location() {
        return $this->belongsTo(ShopifyShopLocation::class, 'shopify_location_id', 'shopify_location_id');
    }
}
