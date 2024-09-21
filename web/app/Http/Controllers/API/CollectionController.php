<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\ShopifyCollection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $session = $request->get('shopifySession');
        $collections = ShopifyCollection::where(['shopify_session_id' => $session->getId()])->get();
        return response()->json(["collections" => $collections]);
    }
}
