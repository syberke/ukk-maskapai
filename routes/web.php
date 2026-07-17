<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('flights.search');
Route::get('/flights/{id}', [FlightController::class, 'show'])->name('flights.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::post('/payments/midtrans/notification', [PaymentController::class, 'notification'])
    ->name('payments.notification');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', EmailVerificationPromptController::class)
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed:relative', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create/{flightId}', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('bookings.store');
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('bookings.review');

    Route::get('/payments/{booking}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{booking}/finish', [PaymentController::class, 'finish'])->name('payments.finish');
    Route::post('/payments/{booking}/sync', [PaymentController::class, 'sync'])
        ->middleware('throttle:10,1')
        ->name('payments.sync');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/flights', [AdminController::class, 'flights'])->name('admin.flights');
    Route::get('/flights/create', [AdminController::class, 'createFlight'])->name('admin.flights.create');
    Route::post('/flights', [AdminController::class, 'storeFlight'])->name('admin.flights.store');
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('admin.bookings');
    Route::put('/bookings/{booking}/status', [AdminController::class, 'updateBookingStatus'])
        ->name('admin.bookings.updateStatus');
    Route::put('/bookings/{booking}/complete', [AdminController::class, 'completeBooking'])
        ->name('admin.bookings.complete');
});
