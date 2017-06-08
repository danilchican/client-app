<?php

use Illuminate\Http\Request;

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


$api = app(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->post('authenticate', 'App\Http\Controllers\Api\Auth\AuthController@login');
    $api->post('register', 'App\Http\Controllers\Api\Auth\RegisterController@register');
});

$api->version('v1', ['middleware' => 'jwt.auth'], function($api) {
    $api->post('payment/create', 'App\Http\Controllers\Api\Payment\PaymentController@create');
    $api->post('payment/remove', 'App\Http\Controllers\Api\Payment\PaymentController@remove');
    $api->get('payment/list/{change?}', 'App\Http\Controllers\Api\Payment\PaymentController@payments');
    $api->get('payment/{number}', 'App\Http\Controllers\Api\Payment\PaymentController@getPaymentByNumber');
    $api->post('payment/update/{number}', 'App\Http\Controllers\Api\Payment\PaymentController@update');
    $api->post('payment/transfer', 'App\Http\Controllers\Api\Payment\PaymentController@transfer');

    $api->get('history', 'App\Http\Controllers\Api\History\HistoryController@history');
    $api->post('history/clear', 'App\Http\Controllers\Api\History\HistoryController@clear');
    $api->post('history/create', 'App\Http\Controllers\Api\History\HistoryController@create');

    $api->get('bill/list/{type?}', 'App\Http\Controllers\Api\Bill\BillController@bills');
    $api->post('bill/remove', 'App\Http\Controllers\Api\Bill\BillController@remove');
    $api->post('bill/create', 'App\Http\Controllers\Api\Bill\BillController@create');

    $api->get('user', 'App\Http\Controllers\Api\Auth\AuthController@getAuthUser');
    $api->post('logout', 'App\Http\Controllers\Api\Auth\AuthController@logout');
});