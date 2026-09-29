<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\AdminContentController;
use App\Http\Controllers\Api\AdminStatsController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Support\LocationCatalog;

// Rutas publicas (no requieren autenticacion)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/ranking', [ProgressController::class, 'ranking']);
Route::get('/content', [ContentController::class, 'index']);
Route::get('/locations', fn () => response()->json([
    'countries' => LocationCatalog::countries(),
    'states' => collect(LocationCatalog::countries())
        ->mapWithKeys(fn (string $country) => [$country => LocationCatalog::states($country)])
        ->filter()->all(),
]));

Route::get('/payments/config', [PaymentController::class, 'config']);
Route::post('/payments/checkout', [PaymentController::class, 'checkout']);
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

// Progreso en modo invitado (ninos sin cuenta)
Route::post('/progress/guest', [ProgressController::class, 'store']);

// Rutas protegidas con autenticacion Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Progreso del usuario autenticado
    Route::get('/progress', [ProgressController::class, 'mine']);
    Route::post('/progress', [ProgressController::class, 'store']);

    // Vista del padre — progreso del hijo vinculado a la cuenta del padre
    Route::get('/parent/child-progress', [ProgressController::class, 'childProgress']);

    // Aulas
    Route::get('/classrooms/mine', [ClassroomController::class, 'mine']);
    Route::post('/classrooms/join', [ClassroomController::class, 'join']);

    // Administracion de materias y actividades (requiere autenticacion)
    Route::prefix('admin')->group(function () {
        Route::get('/content', [AdminContentController::class, 'index']);
        Route::get('/stats', [AdminStatsController::class, 'index']);
        Route::post('/subjects', [AdminContentController::class, 'storeSubject']);
        Route::put('/subjects/{subject}', [AdminContentController::class, 'updateSubject']);
        Route::delete('/subjects/{subject}', [AdminContentController::class, 'destroySubject']);
        Route::post('/levels', [AdminContentController::class, 'storeLevel']);
        Route::put('/levels/{level}', [AdminContentController::class, 'updateLevel']);
        Route::delete('/levels/{level}', [AdminContentController::class, 'destroyLevel']);
        Route::post('/activities', [AdminContentController::class, 'storeActivity']);
        Route::put('/activities/{activity}', [AdminContentController::class, 'updateActivity']);
        Route::delete('/activities/{activity}', [AdminContentController::class, 'destroyActivity']);
    });
});

Route::middleware(['auth:sanctum', 'teacher'])->prefix('teacher')->group(function () {
    Route::get('/classrooms', [ClassroomController::class, 'teacherIndex']);
    Route::post('/classrooms', [ClassroomController::class, 'store']);
    Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroy']);
    Route::put('/classrooms/{classroom}/activities', [ClassroomController::class, 'updateActivities']);
    Route::get('/activities', [ClassroomController::class, 'activities']);
});
