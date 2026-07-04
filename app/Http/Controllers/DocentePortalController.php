<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Asistencia;
use App\Models\Alumno;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocentePortalController extends Controller
{
    /**
     * Devuelve las clases futuras (y del día) asignadas al docente
     */
    public function agenda(Request $request, $id)
    {
        // En el futuro, sacar el $id del Auth::user()->docente->id
        $docente = Docente::findOrFail($id);

        $hoy = $request->query('start', Carbon::today()->toDateString());
        $semana_que_viene = $request->query('end', Carbon::today()->addDays(7)->toDateString());

        $grupos = Grupo::with(['materia', 'aula', 'alumnos' => function ($q) {
                // Solo necesitamos datos básicos, el saldo del alumno y sus packs para calcular deuda
                $q->select('alumnos.id', 'alumnos.nombre', 'alumnos.saldo_clases')
                  ->with(['packsClases' => function($q2) {
                      $q2->whereNull('pack_origen_id')
                         ->where('estado', '!=', 'rechazado');
                  }]);
            }])
            ->where('docente_id', $docente->id)
            ->where('estado', 'activo')
            ->whereDate('fecha', '>=', $hoy)
            ->whereDate('fecha', '<=', $semana_que_viene)
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return response()->json([
            'docente' => [
                'id' => $docente->id,
                'nombre' => $docente->nombre,
                'alias' => $docente->alias,
            ],
            'agenda' => $grupos
        ]);
    }

    /**
     * Registra la asistencia de una clase (Grupo)
     */
    public function registrarAsistencia(Request $request, $grupo_id)
    {
        $grupo = Grupo::with('alumnos')->findOrFail($grupo_id);
        
        $data = $request->validate([
            'asistencias' => 'required|array',
            'asistencias.*.alumno_id' => 'required|exists:alumnos,id',
            'asistencias.*.estado' => 'required|in:presente,ausente,justificado',
            'asistencias.*.observaciones' => 'nullable|string',
            'docente_id' => 'required|exists:docentes,id', // Provisional hasta auth
        ]);

        DB::transaction(function () use ($grupo, $data) {
            foreach ($data['asistencias'] as $item) {
                $alumno = Alumno::find($item['alumno_id']);
                
                $descuenta = in_array($item['estado'], ['presente', 'ausente']);

                // Crear el registro de asistencia
                Asistencia::create([
                    'grupo_id' => $grupo->id,
                    'alumno_id' => $alumno->id,
                    'estado' => $item['estado'],
                    'descuenta_clase' => $descuenta,
                    'observaciones' => $item['observaciones'] ?? null,
                    'registrado_por' => $data['docente_id'],
                ]);

                // Descontar saldo si corresponde
                if ($descuenta) {
                    $alumno->decrement('saldo_clases', 1);
                }
            }

            // Marcar el grupo como finalizado
            $grupo->update(['estado' => 'finalizado']);
        });

        return response()->json(['message' => 'Asistencia registrada correctamente y clase finalizada.']);
    }

    /**
     * Devuelve las estadísticas del docente para un mes específico
     */
    public function estadisticas(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);
        
        $mes = $request->query('mes', Carbon::now()->month);
        $anio = $request->query('anio', Carbon::now()->year);

        // Clases finalizadas (Grupos) dictadas por este docente en ese mes
        $clasesDictadas = Grupo::with('materia')
            ->where('docente_id', $docente->id)
            ->where('estado', 'finalizado')
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->get();

        $cantidadClases = $clasesDictadas->count();

        // Horas por materia
        $horasPorMateria = [];
        foreach ($clasesDictadas as $clase) {
            $materiaNombre = $clase->materia->nombre ?? 'Sin Materia';
            
            // Calcular diferencia de horas
            $inicio = Carbon::parse($clase->hora_inicio);
            $fin = Carbon::parse($clase->hora_fin);
            $horas = $fin->diffInMinutes($inicio) / 60;

            if (!isset($horasPorMateria[$materiaNombre])) {
                $horasPorMateria[$materiaNombre] = 0;
            }
            $horasPorMateria[$materiaNombre] += $horas;
        }

        // Total cobrado (Pagos registrados por este docente en ese mes)
        // Buscamos PacksClases donde cargado_por sea el user_id de este docente.
        $totalCobrado = \App\Models\PackClase::where('cargado_por', $docente->user_id)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->sum('monto_pagado');

        return response()->json([
            'clases_mes' => $cantidadClases,
            'horas_por_materia' => $horasPorMateria,
            'pagos_recibidos' => $totalCobrado,
        ]);
    }
}
