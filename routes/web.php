<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\AiModelController;
use App\Http\Controllers\AiPromptController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/playground', [App\Http\Controllers\PlaygroundController::class, 'index'])->name('playground');

    Route::resource('categories', CategoryController::class);
    Route::resource('types', TypeController::class);
    Route::resource('ai-models', AiModelController::class);
    Route::resource('ai-prompts', AiPromptController::class);
    Route::resource('subscription-plans', App\Http\Controllers\SubscriptionPlanController::class);
    Route::resource('generation-jobs', App\Http\Controllers\GenerationJobController::class)->only(['index', 'show', 'destroy']);
    Route::resource('explore-feed', App\Http\Controllers\ExploreFeedController::class);
    Route::resource('users', App\Http\Controllers\UserController::class)->only(['index', 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
