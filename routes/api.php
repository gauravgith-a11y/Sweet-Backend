<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\category\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\CartController;
use App\Models\Product;
use App\Models\Category;
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


Route::post('/signup', [AuthController::class, 'signup']);
Route::get('/allUser', [AuthController::class, 'allUser']);
// Route::post('/login', [AuthController::class, 'login']);
Route::middleware('web')->post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/test-cors', function () {
    return response()->json(['status' => 'CORS working']);
});


// Category

Route::prefix('category')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);          // Get all
    Route::post('/store', [CategoryController::class, 'store']);    // Create
    // Route::get('/{id}', [CategoryController::class, 'show']);       // Get single
    Route::put('/update/{id}', [CategoryController::class, 'update']); // Update
    Route::delete('/delete/{id}', [CategoryController::class, 'destroy']); // Delete
});

// Product
Route::prefix('product')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/store', [ProductController::class, 'store']);
    Route::post('/update/{id}', [ProductController::class, 'update']); // keep this for method spoofing
    Route::delete('/delete/{id}', [ProductController::class, 'destroy']);
});

Route::post('/delivery', [DeliveryController::class, 'store']);
Route::get('/delivery/pending', [DeliveryController::class, 'pendingOrders']);
Route::post('/delivery/razorpay/{id}', [DeliveryController::class, 'createRazorpayOrder']);
Route::post('/delivery/pay/{id}', [DeliveryController::class, 'payOrder']);
// Route::post('/delivery/pay/{id}', [DeliveryController::class, 'payOrder']);


Route::get('/cart/{userId}', [CartController::class, 'getUserCart']);


Route::get('/dashboard-stats', function () {
    $productCount = Product::count();
    $categoryCount = Category::count();
    return response()->json([
        'productCount' => $productCount,
        'categoryCount' => $categoryCount,
    ]);
});