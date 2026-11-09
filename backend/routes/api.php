<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SummaryController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => response()->json(['status' => 'ok']));

// rotas públicas: sem token
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// rotas protegidas: sem token válido, 401
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::get('/summary', SummaryController::class);

    // exportação em segundo plano: POST responde 202, GET mostra o status, download quando pronta
    Route::post('/exports', [ExportController::class, 'store']);
    Route::get('/exports/{export}', [ExportController::class, 'show']);
    Route::get('/exports/{export}/download', [ExportController::class, 'download'])->name('exports.download');
});
