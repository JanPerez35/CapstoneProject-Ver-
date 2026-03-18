<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear 50 usuarios falsos con rol 'user'
        User::factory()->count(50)->create();

        // Crear un usuario fijo para pruebas
        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'role' => 'user', // rol por defecto
            'password' => bcrypt('password'), // contraseña de prueba
        ]);
    }
}