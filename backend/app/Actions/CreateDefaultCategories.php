<?php

namespace App\Actions;

use App\Models\User;

// categorias que toda conta nova recebe; também usadas pelo seeder
class CreateDefaultCategories
{
    public const DEFAULTS = [
        ['name' => 'Mercado',     'color' => '#22c55e'],
        ['name' => 'Transporte',  'color' => '#3b82f6'],
        ['name' => 'Moradia',     'color' => '#f97316'],
        ['name' => 'Lazer',       'color' => '#a855f7'],
        ['name' => 'Saúde',       'color' => '#ef4444'],
    ];

    public function handle(User $user): void
    {
        foreach (self::DEFAULTS as $category) {
            // firstOrCreate: não duplica se já existir
            $user->categories()->firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
