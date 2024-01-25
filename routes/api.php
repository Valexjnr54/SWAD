<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\admin\AdminAuthController;
use App\Http\Controllers\api\agent\AgentAuthController;
use App\Http\Controllers\api\agent\AgentTransactionsController;
use App\Http\Controllers\api\agent\FacebookController;
use App\Http\Controllers\api\agent\PropertyController;
use App\Http\Controllers\api\agent\PropertySalesController;
use App\Http\Controllers\api\agent\TwitterController;
use App\Http\Controllers\api\PaymentController;

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

Route::prefix('v1')->group(function () {


    //Admin Authentication Route Starts
    Route::prefix('admin')->group(function()
    {
        // Route::group(['middleware' => ['admins']], function () {
            Route::post('/register', [AdminAuthController::class, 'register']);
            Route::post('/login', [AdminAuthController::class, 'login']);
            Route::get('/profile', [AdminAuthController::class, 'profile']);
            Route::post('/logout', [AdminAuthController::class, 'logout']);
        // });
    });
    //Admin Authentication Route Ends

    //Agent Authentication Route Starts
    Route::prefix('agent')->group(function()
    {
        Route::group(['middleware' => ['api']], function () {
            Route::post('/register', [AgentAuthController::class, 'register']);
            Route::post('/login', [AgentAuthController::class, 'login']);
            Route::get('/profile', [AgentAuthController::class, 'profile']);
            Route::get('/confirm-email', [AgentAuthController::class, 'confirmEmail']);
            Route::post('/logout', [AgentAuthController::class, 'logout']);
        });
    });
    //Agent Authentication Route Ends

    //Agent Property Route Starts
    Route::prefix('agent/property')->group(function()
    {
        Route::group(['middleware' => ['api']], function () {
            Route::post('/create-property', [PropertyController::class, 'createProperty']);
            Route::post('/update-property', [PropertyController::class, 'updateProperty']);
            Route::get('/view-all-properties', [PropertyController::class, 'viewAllProperty']);
            Route::get('/view-single-property', [PropertyController::class, 'viewSingleProperty']);
            Route::delete('/delete-property', [PropertyController::class, 'deleteProperty']);

            Route::get('/view-single-sold-property', [PropertySalesController::class, 'viewSingleSoldProperty']);
            Route::get('/view-all-sold-properties', [PropertySalesController::class, 'allSoldProperty']);
        });
    });
    //Agent Property Route Ends

    // Agent Transaction Route Starts
    Route::prefix('agent/transactions')->group(function()
    {
        Route::group(['middleware' => ['api']], function(){
            Route::get('/all-transactions', [AgentTransactionsController::class, 'allTransactions']);
            Route::get('/pending-transactions', [AgentTransactionsController::class, 'pendingTransactions']);
            Route::get('/successful-transactions', [AgentTransactionsController::class, 'successfulTransactions']);
        });
    });
    // Agent Transaction Route Ends

    //Agent Property Share Route Starts
    Route::prefix('agent/share')->group(function()
    {
        Route::group(['middleware' => ['api']], function () {
            Route::get('/auth/facebook', [FacebookController::class, 'getAuthUrl']);
            Route::get('/auth/facebook/callback', [FacebookController::class, 'handleCallback']);
            Route::post('/share-property', [FacebookController::class, 'shareProperty']);
            Route::get('/auth/twitter', [TwitterController::class, 'connect_twitter']);
            Route::get('/auth/twitter/callback', [TwitterController::class, 'twitter_cbk']);
            Route::post('/twitter-share-property', [TwitterController::class, 'shareProperty']);
        });
    });
    //Agent Property Share Route Ends

    Route::post('/customer-payment', [PaymentController::class, 'initializePayment']);
    Route::get('/checkout/paystack-callback', [PaymentController::class, 'verifyPayment']);
    Route::get('/summary/payment-summary', [PaymentController::class, 'summaryPayment']);
});
