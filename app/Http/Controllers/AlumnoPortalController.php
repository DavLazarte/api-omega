<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Asistencia;
use App\Models\PackClase;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AlumnoPortalController extends Controller
{
    public function dashboard(Request $request, $id)
    {
        $alumno = Alumno::with(['solicitudesMaterias.materia', 'solicitudesMaterias.tema'])->findOrFail($id);

        // Último pack comprado
        $ultimoPack = PackClase::with('packCatalogo')
            ->where('alumno_id', $alumno->id)
            ->where('estado', 'validado')
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'alumno' => [
                'id' => $alumno->id,
                'nombre' => $alumno->nombre,
                'saldo_clases' => $alumno->saldo_clases,
                'solicitudes_materias' => $alumno->solicitudesMaterias,
            ],
            'ultimo_pack' => $ultimoPack
        ]);
    }

    public function grupos(Request $request, $id)
    {
        $alumno = Alumno::findOrFail($id);
        $hoy = Carbon::today()->toDateString();

        $grupos = Grupo::with(['materia', 'docente', 'aula', 'alumnos' => function ($q) {
                $q->select('alumnos.id', 'alumnos.nombre');
            }])
            ->whereHas('alumnos', function ($q) use ($alumno) {
                $q->where('alumnos.id', $alumno->id);
            })
            ->where('estado', 'activo')
            ->whereDate('fecha', '>=', $hoy)
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return response()->json($grupos);
    }

    public function historial(Request $request, $id)
    {
        $alumno = Alumno::findOrFail($id);

        $asistencias = Asistencia::with(['grupo.materia', 'docente'])
            ->where('alumno_id', $alumno->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(function ($asis) {
                return [
                    'id' => $asis->id,
                    'fecha' => $asis->grupo?->fecha ?? null,
                    'materia' => $asis->grupo?->materia?->nombre ?? 'N/A',
                    'docente' => $asis->docente?->nombre ?? $asis->docente?->alias ?? 'N/A',
                    'estado' => $asis->estado,
                    'descuenta_clase' => $asis->descuenta_clase,
                ];
            });

        return response()->json($asistencias);
    }
}
