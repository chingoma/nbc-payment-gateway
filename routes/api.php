<?php

use Illuminate\Support\Facades\Route;
use Lockminds\CrdbPaymentGateway\Controllers\CrdbGatewayController;

Route::post('push-callback', [CrdbGatewayController::class, 'callback']);

Route::post('callback', [CrdbGatewayController::class, 'callback']);

Route::post('wallet-to-account', [CrdbGatewayController::class, 'wallet_to_account']);

Route::group(['middleware' => []], function () {

    Route::post('api-token', [CrdbGatewayController::class, 'create_token']);

    Route::post('push-bill-pay', [CrdbGatewayController::class, 'push_bill_pay']);

    Route::post('account-to-wallet', [CrdbGatewayController::class, 'account_to_wallet']);

    Route::post('w2a', [CrdbGatewayController::class, 'w2a']);

    Route::post('refund-transaction', [CrdbGatewayController::class, 'refund_transaction']);

    Route::get('transactions', [CrdbGatewayController::class, 'transactions']);

});
