<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'API Laravel fonctionne']);
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
