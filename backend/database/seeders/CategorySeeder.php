<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@cofrinho.test')->firstOrFail();

        $categorias = [
            ['name' => 'Mercado',     'color' => '#22c55e'],
            ['name' => 'Transporte',  'color' => '#3b82f6'],
            ['name' => 'Moradia',     'color' => '#f97316'],
            ['name' => 'Lazer',       'color' => '#a855f7'],
            ['name' => 'Saúde',       'color' => '#ef4444'],
        ];

        foreach ($categorias as $categoria) {
            // firstOrCreate: só insere se ainda não existir, então dá para rodar o seed mais de uma vez
            $user->categories()->firstOrCreate(['name' => $categoria['name']], $categoria);
        }
    }
}
