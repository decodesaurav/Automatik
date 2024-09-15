<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyShop extends Model
{
    use HasFactory;
	protected $fillable = ['session_id','name','domain','token','current_package_id','is_trial','trial_ends_at','api_version','billing_activated','country','currency','email','phone','shop_owner','timezone','shopify_shop_id','last_synced_at'];

}
