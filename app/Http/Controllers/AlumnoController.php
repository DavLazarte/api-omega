<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\User;
use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AlumnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Alumno::query()->with('user');

        // Búsqueda por nombre o email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAlumnoRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $user = null;

            // Si se solicita crear usuario y hay un email
            if ($request->crear_usuario && isset($data['email'])) {
                $user = User::create([
                    'name' => $data['nombre'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'), // Contraseña por defecto
                    'role' => 'alumno',
                ]);
                $data['user_id'] = $user->id;
            }

            $alumno = Alumno::create($data);

            return response()->json([
                'message' => 'Alumno creado exitosamente',
                'alumno' => $alumno->load('user'),
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Alumno $alumno)
    {
        $alumno->load([
            'user',
            'grupos' => function ($q) {
                $q->with(['materia.instituciones', 'materia.niveles', 'docente', 'aula'])
                    ->orderBy('fecha', 'desc');
            },
            'packsClases' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'solicitudesMaterias' => function ($q) {
                $q->with(['materia', 'tema'])
                    ->orderBy('created_at', 'desc');
            },
        ]);

        return response()->json($alumno);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAlumnoRequest $request, Alumno $alumno)
    {
        $data = $request->validated();

        $alumno->update($data);

        return response()->json([
            'message' => 'Alumno actualizado exitosamente',
            'alumno' => $alumno->load('user'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Alumno $alumno)
    {
        return DB::transaction(function () use ($alumno) {
            // Si tiene usuario asociado, podriamos querer manejarlo
            // Por ahora solo eliminamos el alumno
            // El usuario podria quedar para registros historicos o eliminarse tambien
            $alumno->delete();

            return response()->json([
                'message' => 'Alumno eliminado exitosamente'
            ]);
        });
    }

    /**
     * Assign a user account to an existing alumno.
     */
    public function assignUser(Request $request, Alumno $alumno)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email|unique:alumnos,email,' . $alumno->id,
        ], [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado en el sistema.',
        ]);

        return DB::transaction(function () use ($request, $alumno) {
            if ($alumno->user_id) {
                return response()->json(['message' => 'Este alumno ya tiene un usuario asignado'], 400);
            }

            $user = User::create([
                'name' => $alumno->nombre,
                'email' => $request->email,
                'password' => Hash::make('password'),
                'role' => 'alumno',
            ]);

            $alumno->update([
                'user_id' => $user->id,
                'email' => $request->email,
            ]);

            return response()->json([
                'message' => 'Usuario asignado exitosamente',
                'alumno' => $alumno->load('user'),
            ]);
        });
    }
}
