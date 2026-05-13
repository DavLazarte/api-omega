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
use App\Http\Controllers\PackCatalogoController;
use App\Http\Controllers\PackClaseController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DocenteDisponibilidadController;
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

    // ── Accesibles por admin y docentes ──────────────────────────────
    // Pack Catalogos (lectura para docentes, escritura solo admin)
    Route::get('pack-catalogos', [PackCatalogoController::class, 'index']);
    Route::get('pack-catalogos/{packCatalogo}', [PackCatalogoController::class, 'show']);

    // Packs/Pagos: docentes pueden crear (desde su grupo), admin gestiona todo
    Route::get('packs-clases', [PackClaseController::class, 'index']);
    Route::post('packs-clases', [PackClaseController::class, 'store']);

    // Configuracion: lectura pública (autenticada)
    Route::get('configuracion', [ConfiguracionController::class, 'show']);

    // Disponibilidad docentes: lectura para todos (agente incluido)
    Route::get('docentes-disponibilidad', [DocenteDisponibilidadController::class, 'index']);
    Route::get('docentes-disponibles-hoy', [DocenteDisponibilidadController::class, 'disponiblesHoy']);

    // ── Solo admin ───────────────────────────────────────────────────
    Route::middleware('role:admin', 'auth:sanctum')->group(function () {
        Route::apiResource('alumnos', AlumnoController::class);
        Route::post('alumnos/{alumno}/assign-user', [AlumnoController::class, 'assignUser']);

        Route::apiResource('docentes', DocenteController::class);
        Route::post('docentes/{docente}/assign-user', [DocenteController::class, 'assignUser']);

        Route::apiResource('materias', MateriaController::class);
        Route::apiResource('aulas', AulaController::class);

        // Agrupamientos y Grupos
        Route::apiResource('temas', TemaController::class);
        Route::apiResource('solicitudes-materias', SolicitudMateriaController::class)
            ->parameters(['solicitudes-materias' => 'solicitud']);
        Route::patch('solicitudes-materias/{solicitud}/estado', [SolicitudMateriaController::class, 'updateEstado']);
        Route::apiResource('grupos', GrupoController::class);
        Route::post('grupos/{grupo}/add-alumno', [GrupoController::class, 'addAlumno']);

        Route::apiResource('instituciones', InstitucionController::class)
            ->parameters(['instituciones' => 'institucion']);
        Route::apiResource('niveles', NivelController::class)
            ->parameters(['niveles' => 'nivel']);

        // Pack Catalogos: escritura solo admin
        Route::post('pack-catalogos', [PackCatalogoController::class, 'store']);
        Route::put('pack-catalogos/{packCatalogo}', [PackCatalogoController::class, 'update']);
        Route::patch('pack-catalogos/{packCatalogo}', [PackCatalogoController::class, 'update']);
        Route::delete('pack-catalogos/{packCatalogo}', [PackCatalogoController::class, 'destroy']);

        // Validación y gestión de pagos: solo admin
        Route::patch('packs-clases/{pack}/validar', [PackClaseController::class, 'validar']);
        Route::patch('packs-clases/{pack}/rechazar', [PackClaseController::class, 'rechazar']);
        Route::post('packs-clases/{pack}/pagar-deuda', [PackClaseController::class, 'pagarDeuda']);

        // Disponibilidad de docentes: escritura solo admin
        Route::post('docentes-disponibilidad', [DocenteDisponibilidadController::class, 'store']);
        Route::put('docentes-disponibilidad/{disponibilidad}', [DocenteDisponibilidadController::class, 'update']);
        Route::patch('docentes-disponibilidad/{disponibilidad}', [DocenteDisponibilidadController::class, 'update']);
        Route::delete('docentes-disponibilidad/{disponibilidad}', [DocenteDisponibilidadController::class, 'destroy']);

        // Configuracion: escritura solo admin
        Route::put('configuracion', [ConfiguracionController::class, 'update']);
    });
});

