<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyProduct extends Model
{
    use HasFactory;
	protected $fillable = [
        'session_id',
        'shopify_session_id',
        'shopify_product_id',
        'shopify_product_url',
        'ebay_product_id',
        'descriptionHtml',
        'handle',
        'title',
		'image',
        'product_type',
        'status',
        'tags',
		'metafield',
        'vendor',
        'image_src',
        'isVariable',
        'upload_status',
        'profile_id',
    ];
}
