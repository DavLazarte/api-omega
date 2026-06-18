<?php

namespace App\Http\Controllers;

use App\Models\Egreso;
use App\Models\Docente;
use App\Models\Grupo;
use App\Models\PackClase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EgresoController extends Controller
{
    /**
     * Display a listing of egresos.
     */
    public function index(Request $request)
    {
        $query = Egreso::with('docente');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('concepto', 'like', "%{$search}%");
        }

        if ($request->has('docente_id')) {
            $query->where('docente_id', $request->docente_id);
        }

        if ($request->has('mes_ejercicio')) {
            $query->where('mes_ejercicio', $request->mes_ejercicio);
        }

        if ($request->has('desde')) {
            $query->whereDate('fecha', '>=', $request->desde);
        }

        if ($request->has('hasta')) {
            $query->whereDate('fecha', '<=', $request->hasta);
        }

        $egresos = $query->orderBy('fecha', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate($request->per_page ?? 15);

        return response()->json($egresos);
    }

    /**
     * Store a newly created egreso.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'metodo_pago' => 'required|in:transferencia,efectivo,mercado_pago',
            'docente_id' => 'nullable|exists:docentes,id',
            'horas_pagadas' => 'nullable|numeric|min:0',
            'mes_ejercicio' => 'nullable|string|max:7', // YYYY-MM
            'comprobante_path' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $egreso = Egreso::create($request->all());

        return response()->json([
            'message' => 'Egreso registrado correctamente',
            'egreso' => $egreso->load('docente'),
        ], 201);
    }

    /**
     * Display the specified egreso.
     */
    public function show(Egreso $egreso)
    {
        return response()->json($egreso->load('docente'));
    }

    /**
     * Update the specified egreso.
     */
    public function update(Request $request, Egreso $egreso)
    {
        $validator = Validator::make($request->all(), [
            'concepto' => 'sometimes|required|string|max:255',
            'monto' => 'sometimes|required|numeric|min:0.01',
            'fecha' => 'sometimes|required|date',
            'metodo_pago' => 'sometimes|required|in:transferencia,efectivo,mercado_pago',
            'docente_id' => 'nullable|exists:docentes,id',
            'horas_pagadas' => 'nullable|numeric|min:0',
            'mes_ejercicio' => 'nullable|string|max:7',
            'comprobante_path' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $egreso->update($request->all());

        return response()->json([
            'message' => 'Egreso actualizado correctamente',
            'egreso' => $egreso->load('docente'),
        ]);
    }

    /**
     * Remove the specified egreso.
     */
    public function destroy(Egreso $egreso)
    {
        $egreso->delete();
        return response()->json(['message' => 'Egreso eliminado correctamente']);
    }

    /**
     * Get consolidado finance report (Incomes vs Expenses)
     */
    public function reporte(Request $request)
    {
        $desde = $request->input('desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->input('hasta', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // 1. Ingresos de alumnos validados
        $ingresosQuery = PackClase::where('estado', 'validado')
            ->whereDate('fecha_pago_informado', '>=', $desde)
            ->whereDate('fecha_pago_informado', '<=', $hasta);

        $totalIngresos = (float) $ingresosQuery->sum('monto_pagado');
        $ingresosList = $ingresosQuery->with('alumno')->orderBy('fecha_pago_informado', 'desc')->get();

        // 2. Egresos
        $egresosQuery = Egreso::whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta);

        $totalEgresos = (float) $egresosQuery->sum('monto');
        $egresosList = $egresosQuery->with('docente')->orderBy('fecha', 'desc')->get();

        // 3. Balance neto
        $balanceNeto = $totalIngresos - $totalEgresos;

        // 4. Histórico mensual (ej: últimos 6 meses para gráficos)
        $historico = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthStr = $monthDate->format('Y-m');
            $monthLabel = $monthDate->translatedFormat('F Y');

            $ingM = (float) PackClase::where('estado', 'validado')
                ->whereYear('fecha_pago_informado', $monthDate->year)
                ->whereMonth('fecha_pago_informado', $monthDate->month)
                ->sum('monto_pagado');

            $egrM = (float) Egreso::whereYear('fecha', $monthDate->year)
                ->whereMonth('fecha', $monthDate->month)
                ->sum('monto');

            $historico[] = [
                'mes' => $monthStr,
                'label' => ucfirst($monthLabel),
                'ingresos' => $ingM,
                'egresos' => $egrM,
                'neto' => $ingM - $egrM,
            ];
        }

        return response()->json([
            'rango' => [
                'desde' => $desde,
                'hasta' => $hasta,
            ],
            'resumen' => [
                'total_ingresos' => $totalIngresos,
                'total_egresos' => $totalEgresos,
                'balance_neto' => $balanceNeto,
            ],
            'ingresos' => $ingresosList,
            'egresos' => $egresosList,
            'historico' => $historico,
        ]);
    }

    /**
     * Calculate teacher worked hours and salary settlement for a specific month.
     */
    public function liquidacion(Request $request, Docente $docente)
    {
        $mes = $request->input('mes', Carbon::now()->format('Y-m')); // YYYY-MM
        
        try {
            $date = Carbon::parse($mes . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Formato de mes inválido. Debe ser YYYY-MM.'], 400);
        }

        // Obtener todos los grupos dictados por el docente que están finalizados
        $grupos = Grupo::with('materia')
            ->where('docente_id', $docente->id)
            ->where('estado', 'finalizado')
            ->whereYear('fecha', $date->year)
            ->whereMonth('fecha', $date->month)
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        $totalHoras = 0.0;
        $totalClases = $grupos->count();
        $clasesDetalle = [];

        foreach ($grupos as $grupo) {
            $inicio = Carbon::parse($grupo->fecha . ' ' . $grupo->hora_inicio);
            $fin = Carbon::parse($grupo->fecha . ' ' . $grupo->hora_fin);
            $duracionMinutos = $inicio->diffInMinutes($fin);
            $duracionHoras = round($duracionMinutos / 60, 2);
            $totalHoras += $duracionHoras;

            $clasesDetalle[] = [
                'id' => $grupo->id,
                'fecha' => $grupo->fecha,
                'materia' => $grupo->materia->nombre ?? 'Materia',
                'hora_inicio' => $grupo->hora_inicio,
                'hora_fin' => $grupo->hora_fin,
                'horas' => $duracionHoras,
            ];
        }

        $diasTrabajados = $grupos->pluck('fecha')->unique()->count();
        $tipoContrato = $docente->tipo_contrato;
        $valorContrato = (float) $docente->valor_contrato;
        $montoCalculado = 0.0;

        // Inferencia de montos según el contrato
        if ($tipoContrato === 'por_hora') {
            $montoCalculado = round($totalHoras * $valorContrato, 2);
        } elseif ($tipoContrato === 'sueldo_fijo') {
            $montoCalculado = $valorContrato;
        } elseif ($tipoContrato === 'por_dia') {
            $montoCalculado = round($diasTrabajados * $valorContrato, 2);
        } // temporal u otros queda en 0 base, administrable por el form

        // Obtener egresos ya registrados para este docente en este mes de ejercicio
        $pagosRealizados = Egreso::where('docente_id', $docente->id)
            ->where('mes_ejercicio', $mes)
            ->orderBy('fecha', 'desc')
            ->get();

        $totalPagado = (float) $pagosRealizados->sum('monto');
        $saldoPendiente = max(0.0, $montoCalculado - $totalPagado);

        return response()->json([
            'mes' => $mes,
            'docente' => [
                'id' => $docente->id,
                'nombre' => $docente->nombre,
                'tipo_contrato' => $tipoContrato,
                'valor_contrato' => $valorContrato,
            ],
            'metricas' => [
                'total_clases' => $totalClases,
                'total_horas' => $totalHoras,
                'dias_trabajados' => $diasTrabajados,
            ],
            'liquidacion' => [
                'monto_calculado' => $montoCalculado,
                'monto_pagado' => $totalPagado,
                'saldo_pendiente' => $saldoPendiente,
            ],
            'clases' => $clasesDetalle,
            'pagos' => $pagosRealizados,
        ]);
    }
}
