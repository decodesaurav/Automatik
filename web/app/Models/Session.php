<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
	protected $fillable = [
        'session_id',
        'shop',
        'is_online',
        'state',
        'user_id',
        'user_first_name',
        'user_last_name',
        'user_email',
        'user_email_verified',
        'account_owner',
        'locale',
        'collaborator',
        'shopify_last_import_at',
        'last_collection_import_at',
    ];
	public function shopifyProducts()
    {
        return $this->hasMany(ShopifyProduct::class);
    }
}
