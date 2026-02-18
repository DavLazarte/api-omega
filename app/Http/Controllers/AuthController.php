<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas'],
            ]);
        }

        // Revoke all previous tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Load relationships based on role
        $userData = $user->toArray();

        if ($user->isDocente()) {
            $user->load('docente');
            $userData['docente'] = $user->docente;
        } elseif ($user->isAlumno()) {
            $user->load('alumno');
            $userData['alumno'] = $user->alumno;
        }

        return response()->json([
            'message' => 'Login exitoso',
            'user' => $userData,
            'token' => $token,
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout exitoso',
        ]);
    }

    /**
     * Get authenticated user data
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // Load relationships based on role
        if ($user->isDocente()) {
            $user->load('docente');
        } elseif ($user->isAlumno()) {
            $user->load('alumno');
        }

        return response()->json([
            'user' => $user,
        ]);
    }
}
