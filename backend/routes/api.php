<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/impersonate/{id}', [AuthController::class, 'impersonate']);
    Route::get('/backup/download', [\App\Http\Controllers\BackupController::class, 'download']);
    Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'stats']);

    Route::apiResource('clients', ClientController::class);
    Route::apiResource('employees', \App\Http\Controllers\EmployeeController::class);
    Route::apiResource('files', \App\Http\Controllers\FileController::class);

    Route::post('/files/{id}/worklogs', [\App\Http\Controllers\WorkLogController::class, 'store']);
    Route::get('/files/{id}/logs', [\App\Http\Controllers\WorkLogController::class, 'getByFile']);
    Route::get('/work-logs', [\App\Http\Controllers\WorkLogController::class, 'index']);
    Route::put('/worklogs/{id}', [\App\Http\Controllers\WorkLogController::class, 'update']);
    Route::delete('/worklogs/{id}', [\App\Http\Controllers\WorkLogController::class, 'destroy']);

    Route::post('/files/{id}/documents', [\App\Http\Controllers\DocumentController::class, 'store']);
    Route::get('/files/{id}/documents', [\App\Http\Controllers\DocumentController::class, 'index']);
    Route::get('/documents/{id}/download', [\App\Http\Controllers\DocumentController::class, 'download']);
    Route::delete('/documents/{id}', [\App\Http\Controllers\DocumentController::class, 'destroy']);

    Route::apiResource('invoices', \App\Http\Controllers\InvoiceController::class)->only(['index', 'store', 'show']);
    Route::post('/invoices/{id}/payments', [\App\Http\Controllers\PaymentController::class, 'store']);

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);

    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index']);
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update']);
});
