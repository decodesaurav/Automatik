<?php

use App\Http\Controllers\API\CollectionController;
use App\Http\Controllers\API\TaskController;
use App\Models\Session;
use App\Module\Shopify\Helper\ShopifyHelper;
use Illuminate\Support\Facades\Route;
use Shopify\Clients\Rest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
	return "Hello API";
});

Route::get("update-product", function () {
	$shop = Session::where('id', 1)->first();
	try {
		$client = new Rest($shop->shop, $shop->access_token);
		$data = $client->put('products/8917224128811', [ 'product'=> ['title' => "damp bird shi* on floor"]]);
		dd($data->getDecodedBody());
	} catch (\Exception $e) {
		dd($e);
	}
});

Route::get("/collections", [CollectionController::class, "index"])->middleware('shopify.auth');
Route::post('/tasks', [TaskController::class, 'store'])->middleware('shopify.auth');
