<?php 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes cho User (cần xác thực)
Route::prefix('user')->middleware(['auth:sanctum'])->group(function () {
    // Giỏ hàng
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::put('cart/{cartId}', [CartController::class, 'update']);
    Route::delete('cart/{cartId}', [CartController::class, 'destroy']);
    Route::delete('cart', [CartController::class, 'clear']);

    // Danh sách yêu thích
    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist', [WishlistController::class, 'store']);
    Route::delete('wishlist/{wishlistId}', [WishlistController::class, 'destroy']);

    // Đánh giá sản phẩm
    Route::post('reviews', [ReviewController::class, 'store']);

    // Đặt hàng
    Route::get('orders', [OrderController::class, 'userOrders']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{orderId}', [OrderController::class, 'show']);

    // Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Lấy danh sách sản phẩm (không cần xác thực)
Route::get('/products', [ProductController::class, 'index']);

// Routes cho Admin (cần xác thực và quyền admin)
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('users', UserController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('product-images', ProductImageController::class)->only(['store', 'destroy']);
    Route::apiResource('reviews', ReviewController::class)->only(['index', 'destroy']);
    Route::apiResource('carts', CartController::class)->only(['index', 'destroy']);
    Route::apiResource('wishlists', WishlistController::class)->only(['index', 'destroy']);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('order-items', OrderItemController::class)->only(['index']);

    // Đăng xuất admin
    Route::post('/logout', [AuthController::class, 'logout']);
});
