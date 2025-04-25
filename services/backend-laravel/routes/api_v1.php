<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});

Route::middleware(['throttle:api', 'auth:sanctum'])->prefix('/admins')->group(function () {
    Route::get('/filtered-logs', [AdminController::class, 'getAllLogs']);
    Route::get('/filtered-posts', [AdminController::class, 'getFilteredPosts']);
//    Route::post('/filtered-posts', [AdminController::class, 'approveOrRejectPost']);
    Route::get('/filtered-comments', [AdminController::class, 'getFilteredComments']);
//    Route::post('/filtered-comments', [AdminController::class, 'approveOrRejectComment']);
});

Route::middleware(['throttle:api'])->prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::get('/{id}', [PostController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [PostController::class, 'store']);
        Route::put('/{id}', [PostController::class, 'update']);
        Route::delete('/{id}', [PostController::class, 'destroy']);
        Route::patch('/{id}/restore', [PostController::class, 'restore']);

        // comment routes
        Route::prefix('/{post}/comments')->group(function () {
            Route::get('/', [CommentController::class, 'index']);
            Route::post('/', [CommentController::class, 'store']);
            Route::put('/{comment}', [CommentController::class, 'update']);
            Route::delete('/{comment}', [CommentController::class, 'destroy']);
            Route::patch('/{comment}/restore', [CommentController::class, 'restore'])->withTrashed();
        });
    });
});

