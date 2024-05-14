<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth.basic')->group(function() {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::put('/tasks/{task}/complete', [TaskController::class, 'markAsCompleted']);

    Route::post('/tasks/{task}/subtasks', [TaskController::class, 'storeSubtask']);
    Route::put('/subtasks/{subtask}', [TaskController::class, 'updateSubtask']);
    Route::delete('/subtasks/{subtask}', [TaskController::class, 'destroySubtask']);
    Route::put('/subtasks/{subtask}/complete', [TaskController::class, 'markSubtaskAsCompleted']);
});
