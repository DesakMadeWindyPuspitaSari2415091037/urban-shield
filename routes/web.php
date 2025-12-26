<?php

use App\Http\Controllers\ChatController;

Route::get('/chat', [ChatController::class, 'index']);
Route::post('/chat/send', [ChatController::class, 'send']);
Route::get('/chat/reset', [ChatController::class, 'resetChat'])->name('chat.reset');
