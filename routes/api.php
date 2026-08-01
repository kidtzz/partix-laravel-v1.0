<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();
    $user->role = $user->roles->first()->name ?? 'Kasir';
    return $user;
});

Route::middleware(['auth:sanctum'])->post('/rpc', [\App\Http\Controllers\Api\RpcController::class, 'handle']);
