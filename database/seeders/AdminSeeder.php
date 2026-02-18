<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@omega.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create test docente user
        User::create([
            'name' => 'Profesor Test',
            'email' => 'docente@omega.com',
            'password' => Hash::make('password'),
            'role' => 'docente',
        ]);

        // Create test alumno user
        User::create([
            'name' => 'Alumno Test',
            'email' => 'alumno@omega.com',
            'password' => Hash::make('password'),
            'role' => 'alumno',
        ]);
    }
}
