<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ManagerAttendanceController;
use App\Http\Controllers\Api\AdminAttendanceController;

Route::middleware('api')->prefix('api/v1')->group(function () {
    // Public Attendance Routes
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak']);
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak']);
    Route::get('/attendance/current-status', [AttendanceController::class, 'getCurrentStatus']);

    // Manager Routes (Requires Manager Role)
    Route::middleware('auth:sanctum', 'role:manager')->prefix('manager')->group(function () {
        Route::get('/attendance-dashboard', [ManagerAttendanceController::class, 'dashboard']);
        Route::get('/team-attendance', [ManagerAttendanceController::class, 'teamAttendance']);
        Route::post('/approve-attendance', [ManagerAttendanceController::class, 'approveAttendance']);
        Route::get('/overtime-requests', [ManagerAttendanceController::class, 'overtimeRequests']);
        Route::post('/approve-overtime', [ManagerAttendanceController::class, 'approveOvertime']);
        Route::post('/generate-report', [ManagerAttendanceController::class, 'generateTeamReport']);
    });

    // Admin Routes (Requires Admin Role)
    Route::middleware('auth:sanctum', 'role:superadmin|admin')->prefix('admin')->group(function () {
        Route::get('/attendance-dashboard', [AdminAttendanceController::class, 'dashboard']);
        Route::post('/attendance-report/export', [AdminAttendanceController::class, 'exportReport']);
        Route::get('/employee/{id}/history', [AdminAttendanceController::class, 'employeeHistory']);
        Route::get('/statistics', [AdminAttendanceController::class, 'statistics']);
    });
});
