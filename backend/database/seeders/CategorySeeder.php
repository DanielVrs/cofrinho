<?php

namespace Database\Seeders;

use App\Actions\CreateDefaultCategories;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(CreateDefaultCategories $createDefaultCategories): void
    {
        $user = User::where('email', 'demo@cofrinho.test')->firstOrFail();

        $createDefaultCategories->handle($user);
    }
}
