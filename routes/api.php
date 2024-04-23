<?php

use Illuminate\Support\Facades\Route;
use Lockminds\NBCPaymentGateway\Controllers\NBCGatewayController;

Route::post('push-callback', [NBCGatewayController::class, 'callback']);

Route::post('callback', [NBCGatewayController::class, 'callback']);

Route::post('wallet-to-account', [NBCGatewayController::class, 'wallet_to_account']);

Route::group(['middleware' => []], function () {

    Route::post('api-token', [NBCGatewayController::class, 'create_token']);

    Route::post('push-bill-pay', [NBCGatewayController::class, 'push_bill_pay']);

    Route::post('account-to-wallet', [NBCGatewayController::class, 'account_to_wallet']);

    Route::post('w2a', [NBCGatewayController::class, 'w2a']);

    Route::post('refund-transaction', [NBCGatewayController::class, 'refund_transaction']);

    Route::get('transactions', [NBCGatewayController::class, 'transactions']);

});
