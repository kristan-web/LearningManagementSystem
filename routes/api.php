<?php

use App\Http\Controllers\Api\V1\AccountController;
use Illuminate\Support\Facades\Route;

// Auth is session-based (routes/web.php). Only the OTP email flow uses the API.
Route::prefix('v1')->group(function () {
    Route::post('/auth/otp/send',   [AccountController::class, 'sendOtp'])->middleware('throttle:3,1,otp-send');
    Route::post('/auth/otp/verify', [AccountController::class, 'verifyOtp'])->middleware('throttle:10,1,otp-verify');
    Route::post('/auth/otp/reset',  [AccountController::class, 'resetPasswordWithOtp'])->middleware('throttle:10,1,otp-reset');
});
