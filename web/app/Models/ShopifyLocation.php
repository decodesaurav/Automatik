<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopifyLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_id',
        'location_id',
        'name',
        'address1',
        'address2',
        'city',
        'zip',
        'province',
        'country',
        'phone',
        'country_code',
        'country_name',
        'province_code',
        'admin_graphql_api_id',
        'localized_country_name',
        'localized_province_name',
        'is_default'
    ];
}
