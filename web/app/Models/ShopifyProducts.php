<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyProducts extends Model
{
	use HasFactory;
	protected $fillable = ['product_id', 'title', 'description'];


	public function variants()
	{
		return $this->hasMany(ShopifyProductsVariant::class);
	}
}
