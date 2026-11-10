<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\SearchController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/search', SearchController::class)->middleware('throttle:60,1')->name('search');
Route::get('/pages/{slug}', PageController::class)->where('slug', '[a-z0-9\-_]+')->name('pages.show');
Route::middleware('module:jobs')->group(function () {
    Route::get('/jobs/{slug}', [JobController::class, 'show'])->where('slug', '[a-z0-9\-_]+')->name('jobs.show');
    Route::post('/jobs/{slug}/apply', [JobController::class, 'apply'])->where('slug', '[a-z0-9\-_]+')
        ->middleware(['auth', 'throttle:5,1'])->name('jobs.apply');
});
Route::middleware('module:news')->group(function () {
    Route::get('/news/{slug}', [NewsController::class, 'show'])->where('slug', '[a-z0-9\-_]+')->name('news.show');
    Route::post('/news/{slug}/comments', [NewsController::class, 'comment'])->where('slug', '[a-z0-9\-_]+')
        ->middleware(['auth', 'throttle:5,1'])->name('news.comments.store');
});
Route::view('/contact', 'contact')->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:3,1')->name('contact.store');
Route::view('/mission', 'about.purpose', ['page' => 'mission'])->name('mission');
Route::view('/vision', 'about.purpose', ['page' => 'vision'])->name('vision');
Route::get('/language/{locale}', LocaleController::class)->name('locale.switch');
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::view('/register', 'auth.register')->middleware('registration.open')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware(['registration.open', 'throttle:5,1']);
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});
Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::view('/dashboard', 'dashboard')->middleware('verified')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::view('/verify-email', 'auth.verify-email')->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard')->with('status', 'Email verified successfully.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return back()->with('status', 'A verification link has been sent to your email.');
    })->middleware('throttle:3,1')->name('verification.send');
    Route::view('/account', 'account')->name('account.edit');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'password'])->middleware('throttle:5,1')->name('account.password');
    Route::delete('/account', [AccountController::class, 'destroy'])->middleware('throttle:5,1')->name('account.destroy');
});
