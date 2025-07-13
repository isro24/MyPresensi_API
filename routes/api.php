<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePermissionController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetToken']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


Route::middleware('auth:api')->group(function () {
    
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/photo/{filename}', [AttendanceController::class, 'getPrivatePhoto']);

    Route::middleware('auth:api', 'role:admin')->group(function () {
        Route::get('/logout', [AuthController::class, 'logout']);

        //Dashboard
        Route::get('/admin/dashboard', [DashboardController::class, 'getAdminDashboard']);

        //Location
        Route::get('/admin/location', [LocationController::class, 'index']);
        Route::post('/admin/location', [LocationController::class, 'store']);
        Route::put('/admin/location/{id}', [LocationController::class, 'update']);
        Route::delete('/admin/location/{id}', [LocationController::class, 'destroy']);

        //Schedule
        Route::get('/admin/schedule', [ScheduleController::class, 'index']);
        // Route::post('/admin/schedule', [ScheduleController::class, 'store']);
        // Route::put('/admin/schedule/{id}', [ScheduleController::class, 'update']);
        // Route::delete('/admin/schedule/{id}', [ScheduleController::class, 'destroy']);

        //Employee Management
        Route::get('/admin/employee', [EmployeeController::class, 'index']);
        Route::post('/admin/employee', [EmployeeController::class, 'store']);
        Route::put('/admin/employee/{id}', [EmployeeController::class, 'update']);
        Route::delete('/admin/employee/{id}', [EmployeeController::class, 'destroy']);

        //Employee Attendance Management
        Route::get('/admin/attendance', [AttendanceController::class, 'getAttendanceByAdmin']);

        //Profile
        Route::get('/admin/profile', [ProfileController::class, 'getAdminProfile']);
        Route::put('/admin/profile/{id}', [ProfileController::class, 'updateAdminProfile']);

        //Employee Permission
        Route::get('/admin/permission', [EmployeePermissionController::class, 'adminIndex']);
        Route::patch('/admin/permission/{id}/status', [EmployeePermissionController::class, 'updateStatus']);

        //Attachment
        Route::get('/admin/permission-file/{filename}', [EmployeePermissionController::class, 'downloadAttachment']);

    });

    Route::middleware('auth:api', 'role:employee')->group(function () {
        Route::get('/logout', [AuthController::class, 'logout']);

        //Dashboard
        Route::get('/employee/dashboard', [DashboardController::class, 'getEmployeeDashboard']);

        //Profile
        Route::get('/employee/profile', [ProfileController::class, 'getEmployeeProfile']);
        Route::put('/employee/profile/{id}', [ProfileController::class, 'updateEmployeeProfile']);

        //Attendance
        Route::get('/employee/attendance', [AttendanceController::class, 'getAttendanceByEmployee']);
        Route::post('/employee/attendance/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('/employee/attendance/clock-out', [AttendanceController::class, 'clockOut']);

        //Employee Permission
        Route::get('/employee/permission', [EmployeePermissionController::class, 'employeeIndex']);
        Route::post('/employee/permission', [EmployeePermissionController::class, 'store']);
        Route::put('/employee/permission/{id}', [EmployeePermissionController::class, 'update']);
        Route::delete('/employee/permission/{id}', [EmployeePermissionController::class, 'destroy']);
        Route::get('/employee/permission/{id}', [EmployeePermissionController::class, 'show']);
    });

});