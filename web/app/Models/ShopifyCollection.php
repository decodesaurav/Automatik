<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyCollection extends Model
{
	use HasFactory;

	protected $fillable = [
		'shopify_collection_id',
		'store_id',
		'title',
		'handle',
		'body_html',
		'collection_type',
		'image_data',
		'published_scope',
		'products_count',
	];
}
