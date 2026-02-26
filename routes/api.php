<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\SolicitudMateriaController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\TemaController;
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
        // Aulas
        Route::apiResource('aulas', AulaController::class);

        // Agrupamientos y Grupos
        Route::apiResource('temas', TemaController::class);
        Route::apiResource('solicitudes-materias', SolicitudMateriaController::class)->parameters(['solicitudes-materias' => 'solicitud']);
        Route::patch('solicitudes-materias/{solicitud}/estado', [SolicitudMateriaController::class, 'updateEstado']);
        Route::apiResource('grupos', GrupoController::class);
        Route::post('grupos/{grupo}/add-alumno', [GrupoController::class, 'addAlumno']);
        Route::apiResource('instituciones', InstitucionController::class)->parameters(['instituciones' => 'institucion']);
        Route::apiResource('niveles', NivelController::class)->parameters(['niveles' => 'nivel']);
    });
});
