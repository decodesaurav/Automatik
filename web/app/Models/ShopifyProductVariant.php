<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopifyProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopify_product_id',
        'variant_id',
        'title',
        'price',
        'sku',
        'inventory_policy',
        'is_tracked',
        'option1',
        'option2',
        'option3',
        'image_id',
        'inventory_item_id',
        'inventory_quantity',
        'session_id',
        'shopify_session_id',
        'weight',
        'weight_unit',
        'is_inventory_syncing',
        'barcode'
    ];

    public function shopifyProduct(): BelongsTo
    {
        return $this->belongsTo(ShopifyProduct::class, 'shopify_product_id');
    }
}
