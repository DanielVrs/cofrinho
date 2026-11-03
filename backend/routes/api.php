<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExportExpensesController;
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
    // antes do apiResource, senão 'export' seria tratado como um id de lançamento
    Route::get('/expenses/export', ExportExpensesController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::get('/summary', SummaryController::class);
});
