<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ComplaintController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);

    // ✅ Student complaint routes
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints/my', [ComplaintController::class, 'myComplaints']);
    Route::post('/student/complaints', [ComplaintController::class, 'studentStore']);
    Route::get('/student/complaints', [ComplaintController::class, 'studentHistory']);

    // ✅ NAYA: Teacher complaint routes
    Route::post('/complaints/submit', [ComplaintController::class, 'teacherSubmit']);
    Route::get('/teacher/complaints', [ComplaintController::class, 'teacherDepartmentComplaints']);
    Route::put('/complaints/update/{id}', [ComplaintController::class, 'teacherUpdateStatus']);

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