<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:email-verify-resend'])
    ->name('verification.verify');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // Email verification
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:email-verify-resend');

    Route::post('/logout', [AuthController::class, 'logout']);

    // Users 
    Route::get('/users', [UserController::class, 'index']);

    // Friendships
    Route::post('/friends/send',   [FriendshipController::class, 'send'])
        ->middleware('throttle:friend-actions');
    Route::post('/friends/accept', [FriendshipController::class, 'accept'])
        ->middleware('throttle:friend-actions');
    Route::get('/friends',         [FriendshipController::class, 'list']);

    // Messages
    Route::post('/messages', [MessageController::class, 'send'])
        ->middleware('throttle:messages');
    Route::get('/messages/{userId}', [MessageController::class, 'conversation'])
        ->middleware('throttle:conversation');
});
