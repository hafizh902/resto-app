<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AdminMenuController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public Routes
Route::get('/', function () {
    return view('home');
})->name('home');

// Static Pages
Route::get('/shop', function () {
    return view('shop');
})->name('shop');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/testimonial', function () {
    return view('testimonial');
})->name('testimonial');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Customer Routes
Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::get('/menu/{id}', [MenuController::class, 'show'])->name('menu.show');
Route::get('/cart', [MenuController::class, 'cart'])->name('cart');
Route::get('/checkout', [MenuController::class, 'checkout'])->name('checkout');

// Admin Routes (Protected)
Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Menu Management
    Route::get('/menu', [AdminMenuController::class, 'index'])->name('admin.menu');
    Route::get('/menu/create', [AdminMenuController::class, 'create'])->name('admin.menu.create');
    Route::post('/menu', [AdminMenuController::class, 'store'])->name('admin.menu.store');
    Route::get('/menu/{id}', [AdminMenuController::class, 'show'])->name('admin.menu.show');
    Route::get('/menu/{id}/edit', [AdminMenuController::class, 'edit'])->name('admin.menu.edit');
    Route::put('/menu/{id}', [AdminMenuController::class, 'update'])->name('admin.menu.update');
    Route::delete('/menu/{id}', [AdminMenuController::class, 'destroy'])->name('admin.menu.destroy');

    // Categories Management
    Route::prefix('categories')->group(function () {
        Route::get('/', [AdminMenuController::class, 'categoriesIndex'])->name('admin.categories');
        Route::post('/', [AdminMenuController::class, 'categoriesStore'])->name('admin.categories.store');
        Route::put('/{id}', [AdminMenuController::class, 'categoriesUpdate'])->name('admin.categories.update');
        Route::delete('/{id}', [AdminMenuController::class, 'categoriesDestroy'])->name('admin.categories.destroy');
    });
});

// Redirects
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
})->name('admin.home');

// Error Page
Route::get('/404', function () {
    return view('errors.404');
})->name('404');
