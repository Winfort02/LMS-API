<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockReturnController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserLogController;

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

// Public users
Route::post('login', LoginController::class);
Route::post('register', RegisterController::class);


// Private Routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('logout', LogoutController::class);
    Route::apiResource('users', UserController::class);


    // Category Routes
    Route::get('categories/dropdown', [CategoryController::class, 'show_all_category']);
    Route::apiResource('categories', CategoryController::class);

    // Brand Routes
    Route::get('brands/dropdown', [BrandController::class, 'show_all_brand']);
    Route::apiResource('brands', BrandController::class);

    // Customer Routes
    Route::get('customers/dropdown', [CustomerController::class, 'show_all_customer']);
    Route::apiResource('customers', CustomerController::class);

    // Supplier Routes
    Route::get('suppliers/dropdown', [SupplierController::class, 'show_all_supplier']);
    Route::apiResource('suppliers', SupplierController::class);


    // Product Routes
    Route::get('products/dropdown', [ProductController::class, 'show_all_product']);
    Route::post('products/inventory/report', [ProductController::class, 'generate_product_inventory']);
    Route::get('products/by-supplier/{id}', [ProductController::class, 'show_all_product_by_supplier']);
    Route::put('products/update/{id}', [ProductController::class, 'update_product']);
    Route::apiResource('products', ProductController::class);

    //Stock In
    Route::post('stock-in/generate-report', [StockInController::class, 'generate_stock_in_report']);
    Route::get('stock-in/by-product/{id}', [StockInController::class, 'show_all_stock_in_by_product']);
    Route::apiResource('stock-in', StockInController::class);

    //Stock Return
    Route::post('stock-return/generate-report', [StockReturnController::class, 'generate_stock_return_report']);
    Route::get('stock-return/by-product/{id}', [StockReturnController::class, 'show_all_stock_return_by_product']);
    Route::apiResource('stock-return', StockReturnController::class);

    //Stock Orders
    Route::post('orders/order-item-summary-report', [OrderController::class, 'generate_order_items_summary_report']);
    Route::get('orders/by-customer/{customer_id}', [OrderController::class, 'show_orders_by_customer']);
    Route::get('orders/by-user/{user_id}', [OrderController::class, 'show_orders_by_user']);
    Route::post('orders/generate/sales-report', [OrderController::class, 'generate_sales_order_report']);
    Route::get('orders/generate-pdf/{id}', [OrderController::class, 'generate_sales_order_pdf']);
    Route::apiResource('orders', OrderController::class);


    //Dashboard
    Route::get('dashboard/product-low-quantity', [DashboardController::class, 'show_product_with_low_quantity']);
    Route::get('dashboard/latest-transaction', [DashboardController::class, 'show_latest_ten_transaction']);
    Route::get('dashboard/product-status/{product_id}', [DashboardController::class, 'generate_product_status_report']);
    Route::get('dashboard/count', [DashboardController::class, 'generate_dashboard_reports']);
    Route::get('dashboard/generate-yearly-sales/{year}', [DashboardController::class, 'generate_yearly_sales_dashboard']);


    //User Logs
    Route::get('user-logs/by-user/{user_id}', [UserLogController::class, 'show_all_logs_by_user']);
});
