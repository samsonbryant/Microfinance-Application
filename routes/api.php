<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\DashboardController as ApiDashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Routes for real-time functionality
Route::middleware(['auth:web'])->group(function () {
    // Notifications API
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Search API
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/search/clients', [SearchController::class, 'searchClients']);
    Route::get('/search/loans', [SearchController::class, 'searchLoans']);
    
    // Dashboard API
    Route::get('/dashboard/stats', [ApiDashboardController::class, 'getStats']);
    Route::get('/dashboard/recent-activities', [ApiDashboardController::class, 'getRecentActivities']);
});

// Public API routes (if needed)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');