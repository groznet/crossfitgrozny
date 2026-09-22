<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NewRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Public\ProfileRequestController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/profile', [ProfileRequestController::class, 'create'])->name('public.profile.create');
Route::post('/profile/send-code', [ProfileRequestController::class, 'sendCode'])
    ->middleware('throttle:otp-send')
    ->name('public.profile.send-code');
Route::post('/profile/resend-code', [ProfileRequestController::class, 'resendCode'])
    ->middleware('throttle:otp-send')
    ->name('public.profile.resend-code');
Route::post('/profile/verify-code', [ProfileRequestController::class, 'verifyCode'])
    ->middleware('throttle:10,1')
    ->name('public.profile.verify-code');
Route::get('/profile/thanks', [ProfileRequestController::class, 'thanks'])->name('public.profile.thanks');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', fn () => redirect()->route('members.index'));

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::post('/members/{member}/archive', [MemberController::class, 'archive'])->name('members.archive');
    Route::post('/members/{member}/restore', [MemberController::class, 'restore'])->name('members.restore');

    Route::get('/members/{member}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/members/{member}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    Route::get('/requests', [NewRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{member}/edit', [NewRequestController::class, 'edit'])->name('requests.edit');
    Route::put('/requests/{member}', [NewRequestController::class, 'approve'])->name('requests.approve');
    Route::delete('/requests/{member}', [NewRequestController::class, 'reject'])->name('requests.reject');
    Route::get('/requests/{member}/merge/{target}', [NewRequestController::class, 'confirmMerge'])->name('requests.merge.confirm');
    Route::post('/requests/{member}/merge/{target}', [NewRequestController::class, 'merge'])->name('requests.merge');

    Route::get('/settings/prices', [SettingsController::class, 'edit'])->name('settings.prices.edit');
    Route::put('/settings/prices', [SettingsController::class, 'update'])->name('settings.prices.update');
});
