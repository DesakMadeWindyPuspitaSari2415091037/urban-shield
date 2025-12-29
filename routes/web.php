<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContributorController;
use Illuminate\Support\Facades\Route;

// 1. AKSES PENGGUNA UMUM (Chatbot)

Route::get('/chat', [ChatController::class, 'index']);
Route::post('/chat/send', [ChatController::class, 'send']);

Route::middleware(['auth'])->group(function () {

    // 2. AKSES KONTRIBUTOR (Isi konten berita/artikel)
    Route::middleware(['role:contributor'])->group(function () {
        Route::get('/contributor/dashboard', [ContributorController::class, 'index'])->name('contributor.dashboard');
        Route::post('/contributor/upload', [ContributorController::class, 'store'])->name('contributor.upload');
    });

    // 3. AKSES ADMINISTRATOR (Kelola sistem & Approve)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/admin/approve/{id}', [AdminController::class, 'approve'])->name('admin.approve');
    });

});