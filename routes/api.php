<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::resource('cliente', 'App\Http\Controllers\ClienteController');
Route::prefix('v1')->middleware(\Tymon\JWTAuth\Http\Middleware\Authenticate::class)->group(function () {
    Route::post('refresh', 'App\Http\Controllers\AuthController@refresh');
    Route::post('logout', 'App\Http\Controllers\AuthController@logout')->name('logout');
    Route::post('me', 'App\Http\Controllers\AuthController@me');
    Route::apiResource('cliente', 'App\Http\Controllers\ClienteController');
    Route::apiResource('carro', 'App\Http\Controllers\CarroController');
    Route::apiResource('locacao', 'App\Http\Controllers\LocacaoController');
    Route::apiResource('marca', 'App\Http\Controllers\MarcaController');
    Route::apiResource('modelo', 'App\Http\Controllers\ModeloController');
});

// ---------------------------------------------------------------------------- //
Route::post('login', 'App\Http\Controllers\AuthController@login');