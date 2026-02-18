<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\AulaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Admin-only CRUDs
    Route::middleware('role:admin', 'auth:sanctum')->group(function () {
        Route::apiResource('alumnos', AlumnoController::class);
        Route::post('alumnos/{alumno}/assign-user', [AlumnoController::class, 'assignUser']);

        Route::apiResource('docentes', DocenteController::class);
        Route::post('docentes/{docente}/assign-user', [DocenteController::class, 'assignUser']);

        Route::apiResource('materias', MateriaController::class);
        Route::apiResource('aulas', AulaController::class);
    });
});
