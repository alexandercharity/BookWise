<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('books.index'));

// Buku (publik)
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// Fitur yang butuh login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [RecommendationController::class, 'index'])->name('dashboard');

    // Rating
    Route::post('/books/{book}/rate', [RatingController::class, 'store'])->name('ratings.store');
    Route::delete('/books/{book}/rate', [RatingController::class, 'destroy'])->name('ratings.destroy');

    // Profile (dari Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
