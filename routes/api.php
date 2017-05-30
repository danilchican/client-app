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
    $api->get('user', 'App\Http\Controllers\Api\Auth\AuthController@getAuthUser');
    $api->post('logout', 'App\Http\Controllers\Api\Auth\AuthController@logout');
});