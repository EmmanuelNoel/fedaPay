<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//FedaPay

//Route::post('fedapay/api/login', [\App\Http\Controllers\FedaPayController::class, 'login']);

Route::post('fedapay/api/transaction', [\App\Http\Controllers\FedaPayController::class, 'createTransaction'])->name('createTransaction');

Route::get('fedapay/api/callback', [\App\Http\Controllers\FedaPayController::class, 'handleCallback'])->name('handleCallback');

Route::get('fedapay/api/transaction/status/{status}', [\App\Http\Controllers\FedaPayController::class, 'transactionStatus'])->name('transactionStatus');

