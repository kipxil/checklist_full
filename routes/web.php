<?php

use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UpsellingItemController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/daily-reports', [DailyReportController::class, 'index'])->name('daily-reports.index');
    Route::get('/daily-reports/create', [DailyReportController::class, 'create'])->name('daily-reports.create');
    Route::post('/daily-reports', [DailyReportController::class, 'store'])->name('daily-reports.store');
    Route::get('/daily-reports/{dailyReport}/edit', [DailyReportController::class, 'edit'])->name('daily-reports.edit');
    Route::put('/daily-reports/{dailyReport}', [DailyReportController::class, 'update'])->name('daily-reports.update');
    Route::get('/daily-reports/{dailyReport}', [DailyReportController::class, 'show'])->name('daily-reports.show');
    Route::patch('/daily-reports/{dailyReport}/approve', [DailyReportController::class, 'approve'])->name('daily-reports.approve');
    Route::patch('/daily-reports/{dailyReport}/reject', [DailyReportController::class, 'reject'])->name('daily-reports.reject');

    Route::get('/upselling-items', [UpsellingItemController::class, 'index'])->middleware(['role:Super Admin'])->name('upselling-items.index');
    Route::get('/upselling-items/create', [UpsellingItemController::class, 'create'])->middleware(['role:Super Admin'])->name('upselling-items.create');
    Route::post('/upselling-items', [UpsellingItemController::class, 'store'])->middleware(['role:Super Admin'])->name('upselling-items.store');
    Route::get('/upselling-items/{upsellingItem}/edit', [UpsellingItemController::class, 'edit'])->middleware(['role:Super Admin'])->name('upselling-items.edit');
    Route::put('/upselling-items/{upsellingItem}', [UpsellingItemController::class, 'update'])->middleware(['role:Super Admin'])->name('upselling-items.update');
    Route::delete('/upselling-items/{upsellingItem}', [UpsellingItemController::class, 'destroy'])->middleware(['role:Super Admin'])->name('upselling-items.destroy');

    Route::get('/restaurants', [RestaurantController::class, 'index'])->middleware(['role:Super Admin'])->name('restaurants.index');
    Route::get('/restaurants/create', [RestaurantController::class, 'create'])->middleware(['role:Super Admin'])->name('restaurants.create');
    Route::post('/restaurants', [RestaurantController::class, 'store'])->middleware(['role:Super Admin'])->name('restaurants.store');
    Route::get('/restaurants/{restaurant}/edit', [RestaurantController::class, 'edit'])->middleware(['role:Super Admin'])->name('restaurants.edit');
    Route::put('/restaurants/{restaurant}', [RestaurantController::class, 'update'])->middleware(['role:Super Admin'])->name('restaurants.update');
    Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy'])->middleware(['role:Super Admin'])->name('restaurants.destroy');

    Route::get('/users', [UserController::class, 'index'])->middleware(['role:Super Admin'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->middleware(['role:Super Admin'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware(['role:Super Admin'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware(['role:Super Admin'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware(['role:Super Admin'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware(['role:Super Admin'])->name('users.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
