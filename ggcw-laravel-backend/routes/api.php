<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    
    Route::middleware('admin')->group(function () {
        // Users
        Route::get('/admin/users/all', [AuthController::class, 'getAllUsers']);
        Route::get('/admin/users/students', [AuthController::class, 'getStudents']);
        Route::get('/admin/users/teachers', [AuthController::class, 'getTeachers']);
        Route::post('/admin/users/approve/{id}', [AuthController::class, 'approveUser']);
        Route::post('/admin/users/reject/{id}', [AuthController::class, 'rejectUser']);
        
        // ✅ Complaints & Feedbacks
        Route::get('/admin/complaints', [AdminController::class, 'getComplaints']);
        Route::get('/admin/feedbacks', [AdminController::class, 'getFeedbacks']);
        Route::post('/admin/complaints/{id}/status', [AdminController::class, 'updateComplaintStatus']);
    });
});