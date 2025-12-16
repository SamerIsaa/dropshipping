<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Webhooks\PaymentWebhookController;
use App\Http\Controllers\TrackingController;
use App\Http\Middleware\VerifyPaymentWebhookSignature;
use App\Http\Middleware\IdempotencyMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::post('/webhooks/payments/{provider}', PaymentWebhookController::class)
    ->middleware(['throttle:30,1', VerifyPaymentWebhookSignature::class, IdempotencyMiddleware::class])
    ->name('webhooks.payments');

Route::get('/track', TrackingController::class)->name('tracking.show');

Route::inertia('/legal/shipping-policy', 'Legal/ShippingPolicy')->name('legal.shipping');
Route::inertia('/legal/refund-policy', 'Legal/RefundPolicy')->name('legal.refund');
Route::inertia('/legal/privacy-policy', 'Legal/PrivacyPolicy')->name('legal.privacy');
Route::inertia('/legal/terms-of-service', 'Legal/TermsOfService')->name('legal.terms');
Route::inertia('/legal/customs-disclaimer', 'Legal/CustomsDisclaimer')->name('legal.customs');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
