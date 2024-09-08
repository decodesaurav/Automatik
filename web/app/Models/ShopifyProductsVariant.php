<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyProductsVariant extends Model
{
	use HasFactory;
	protected $fillable = ['product_id', 'variant_id', 'inventory_item_id'];

	public function product()
	{
		return $this->belongsTo(ShopifyProducts::class);
	}

	public function inventory()
	{
		return $this->hasMany(ProductInventories::class);
	}
}
