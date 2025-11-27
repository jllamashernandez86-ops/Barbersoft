<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario administrador para Filament
        \App\Models\User::query()->firstOrCreate(
            ['email' => 'admin@barbersoft.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin123'), // <- contraseña para Filament
            ]
        );

        // Datos de ejemplo (sin usar factories)
        \App\Models\Cliente::query()->firstOrCreate(
            ['correo' => 'carlos@example.com'],
            ['nombre' => 'Carlos Pérez', 'telefono' => '3001234567']
        );

        \App\Models\Barbero::query()->firstOrCreate(
            ['telefono' => '3007654321'],
            ['nombre' => 'Andrés Barber']
        );

        // Servicios
        $servicios = [
            ['nombre' => 'Corte clásico', 'precio' => 15000, 'duracion' => 30],
            ['nombre' => 'Barba', 'precio' => 10000, 'duracion' => 20],
            ['nombre' => 'Corte + Barba', 'precio' => 23000, 'duracion' => 50],
        ];

        foreach ($servicios as $servicio) {
            \App\Models\Servicio::query()->firstOrCreate(
                ['nombre' => $servicio['nombre']],
                $servicio
            );
        }
    }
}



