<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventories extends Model
{
	use HasFactory;
	protected $fillable = ['product_id', 'inventory_item_id', 'location_id'];

	public function variant()
	{
		return $this->belongsTo(ShopifyProductsVariant::class);
	}
}
