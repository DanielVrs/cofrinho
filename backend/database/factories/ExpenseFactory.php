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
            // se o teste não passar uma categoria, a factory cria uma (com um usuário novo)
            'category_id' => Category::factory(),
            // o lançamento é do mesmo usuário da categoria
            'user_id' => fn (array $attributes) => Category::find($attributes['category_id'])->user_id,
        ];
    }
}
