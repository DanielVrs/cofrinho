<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // conta de demonstração: demo@cofrinho.test / password
        User::firstOrCreate(
            ['email' => 'demo@cofrinho.test'],
            ['name' => 'Demo', 'password' => 'password'],
        );

        $this->call(CategorySeeder::class);
    }
}
