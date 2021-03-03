<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// SERVER STATUS
Route::get('/status', function () {
    return [
        'running' => true
    ];
});

// LOGIN / REGISTER
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [RegisterController::class, 'login']);
Route::post('/login/check', [RegisterController::class, 'check'])->middleware('auth:sanctum');
Route::post('/logout', [RegisterController::class, 'logout'])->middleware('auth:sanctum');

// USERS
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->get('/users/{user}', [UserController::class, 'show']);
Route::middleware('auth:sanctum')->put('/users/{user}', [UserController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/users/{user}', [UserController::class, 'destroy']);

// CONVERSATIONS
Route::middleware('auth:sanctum')->get('/user/conversations', [ConversationController::class, 'index']);
Route::middleware('auth:sanctum')->get('/user/conversations/{conversation}', [ConversationController::class, 'show']);
Route::middleware('auth:sanctum')->post('/user/conversations', [ConversationController::class, 'store']);
Route::middleware('auth:sanctum')->put('/user/conversations/{conversation}', [ConversationController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/user/conversations/{conversation}', [ConversationController::class, 'destroy']);

// MESSAGES
Route::middleware('auth:sanctum')->get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
Route::middleware('auth:sanctum')->get('/conversations/{conversation}/messages/{message}', [MessageController::class, 'show']);
Route::middleware('auth:sanctum')->post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
//Route::middleware('auth:sanctum')->put('/conversations/{conversation}/messages/{message}', [MessageController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/conversations/{conversation}/messages/{message}', [MessageController::class, 'destroy']);
