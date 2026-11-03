<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Expense> */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(3),
            'amount_cents' => fake()->numberBetween(100, 50000),
            'spent_on' => fake()->dateTimeThisYear()->format('Y-m-d'),
            // se o teste não passar uma categoria, a factory cria uma
            'category_id' => Category::factory(),
        ];
    }
}
