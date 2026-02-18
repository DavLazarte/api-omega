<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\User;
use App\Http\Requests\StoreDocenteRequest;
use App\Http\Requests\UpdateDocenteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DocenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Docente::query()->with(['user', 'subjects']);

        // Búsqueda por nombre o email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocenteRequest $request)
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
                    'role' => 'docente',
                ]);
                $data['user_id'] = $user->id;
            }

            $docente = Docente::create($data);

            // Sincronizar materias si vienen IDs
            if ($request->has('materia_ids')) {
                $docente->subjects()->sync($request->materia_ids);
            }

            return response()->json([
                'message' => 'Docente creado exitosamente',
                'docente' => $docente->load(['user', 'subjects']),
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Docente $docente)
    {
        return response()->json($docente->load(['user', 'subjects']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocenteRequest $request, Docente $docente)
    {
        return DB::transaction(function () use ($request, $docente) {
            $data = $request->validated();

            $docente->update($data);

            // Sincronizar materias si vienen IDs
            if ($request->has('materia_ids')) {
                $docente->subjects()->sync($request->materia_ids);
            }

            return response()->json([
                'message' => 'Docente actualizado exitosamente',
                'docente' => $docente->load(['user', 'subjects']),
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Docente $docente)
    {
        return DB::transaction(function () use ($docente) {
            $docente->delete();

            return response()->json([
                'message' => 'Docente eliminado exitosamente'
            ]);
        });
    }

    /**
     * Assign a user account to an existing docente.
     */
    public function assignUser(Request $request, Docente $docente)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email|unique:docentes,email,' . $docente->id,
        ], [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado en el sistema.',
        ]);

        return DB::transaction(function () use ($request, $docente) {
            if ($docente->user_id) {
                return response()->json(['message' => 'Este docente ya tiene un usuario asignado'], 400);
            }

            $user = User::create([
                'name' => $docente->nombre,
                'email' => $request->email,
                'password' => Hash::make('password'),
                'role' => 'docente',
            ]);

            $docente->update([
                'user_id' => $user->id,
                'email' => $request->email,
            ]);

            return response()->json([
                'message' => 'Usuario asignado exitosamente',
                'docente' => $docente->load('user'),
            ]);
        });
    }
}
