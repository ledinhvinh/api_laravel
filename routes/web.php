<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayPalController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
// Route để bắt đầu quá trình thanh toán
Route::get('paypal/payment', [PayPalController::class, 'createPayment'])->name('paypal.payment');

// Route PayPal sẽ gọi lại sau khi thanh toán thành công
Route::get('paypal/success', [PayPalController::class, 'paymentSuccess'])->name('paypal.success');

// Route PayPal sẽ gọi lại nếu người dùng hủy thanh toán
Route::get('paypal/cancel', [PayPalController::class, 'paymentCancel'])->name('paypal.cancel');