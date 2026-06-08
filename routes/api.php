<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\TypeApiController;
use App\Http\Controllers\Api\AiModelApiController;
use App\Http\Controllers\Api\AiPromptApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SubscriptionApiController;
use App\Http\Controllers\Api\GenerationApiController;
use App\Http\Controllers\Api\ExploreApiController;
use App\Http\Controllers\Api\WebhookController;

// Public API Routes
Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/google', [AuthController::class, 'googleLogin']);
    Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::get('/auth/email/verify-temp/{id}/{token}', [AuthController::class, 'verifyTempEmail'])->name('auth.verify-temp');
    Route::post('/auth/email/resend', [AuthController::class, 'resendVerificationEmail']);

    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::get('/categories/{category}', [CategoryApiController::class, 'show']);

    Route::get('/types', [TypeApiController::class, 'index']);
    Route::get('/types/{type}', [TypeApiController::class, 'show']);

    Route::get('/models', [AiModelApiController::class, 'index']);
    Route::get('/models/{aiModel}', [AiModelApiController::class, 'show']);

    // Existing routes mapped to match SRS or kept for backward compatibility
    Route::get('/ai-models', [AiModelApiController::class, 'index']);
    Route::get('/ai-models/{aiModel}', [AiModelApiController::class, 'show']);
    Route::get('/ai-prompts', [AiPromptApiController::class, 'index']);
    Route::get('/ai-prompts/{aiPrompt}', [AiPromptApiController::class, 'show']);
    Route::get('/ai-prompts/{aiPrompt}/similar', [AiPromptApiController::class, 'similar']);

    Route::get('/plans', [SubscriptionApiController::class, 'index']);

    Route::get('/explore', [ExploreApiController::class, 'index']);
    Route::get('/explore/prompts', [ExploreApiController::class, 'prompts']);

    Route::post('/webhooks/{model_slug}', [WebhookController::class, 'handle']);
});

// Protected API Routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/phone/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/auth/phone/verify', [AuthController::class, 'verifyPhone']);

    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me/profile', [AuthController::class, 'updateProfile']);
    Route::post('/me/verify-email-reward', [AuthController::class, 'verifyEmailReward']);
    Route::post('/me/verify-phone-reward', [AuthController::class, 'verifyPhoneReward']);
    Route::post('/me/complete-profile-reward', [AuthController::class, 'completeProfileReward']);
    Route::get('/me/credits/history', [AuthController::class, 'creditHistory']);
    Route::post('/me/credits/claim', [AuthController::class, 'claimCredits']);
    Route::post('/me/credits/watch-ad', [AuthController::class, 'watchAd']);

    Route::get('/me/subscription', [SubscriptionApiController::class, 'mySubscription']);
    Route::post('/me/subscription', [SubscriptionApiController::class, 'subscribe']);
    Route::delete('/me/subscription', [SubscriptionApiController::class, 'unsubscribe']);
    Route::get('/me/credits', [SubscriptionApiController::class, 'myCredits']);

    Route::post('/generations', [GenerationApiController::class, 'store']);
    Route::get('/generations', [GenerationApiController::class, 'index']);
    Route::get('/generations/{job}', [GenerationApiController::class, 'show']);
    Route::get('/generations/history', [GenerationApiController::class, 'index']); // Explicit history route

    Route::get('/me/favorites', [\App\Http\Controllers\Api\FavoriteApiController::class, 'index']);
    Route::post('/ai-prompts/{aiPrompt}/favorite', [\App\Http\Controllers\Api\FavoriteApiController::class, 'toggle']);
});
