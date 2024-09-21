<?php

namespace App\Models;

use App\Models\ShopifyProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'shopify_session_id',
        'shopify_collection_id',
        'handle',
        'title',
        'updated_at_shopify',
        'image_src',
        'profile_id',
    ];

    public function products()
    {
        return $this->belongsToMany(
            ShopifyProduct::class,
            'shopify_products_collections',
            'shopify_collection_id',
            'shopify_product_id',
            'id',
            'shopify_product_id'
        );
    }
}
