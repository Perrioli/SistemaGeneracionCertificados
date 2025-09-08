<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $rootRole = Role::where('name', 'Root')->first();

        // Crear el usuario Root por defecto
        User::create([
            'name' => 'Root', // Nombre del usuario mantengan Root para mejor comprension o cambienlo si lo desean
            'email' => 'root@sistema.com',
            'password' => Hash::make('password'), // Contraseña
            'role_id' => $rootRole->id,
        ]);
    }
}
