<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{



    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        // First, we'll find a random group.
        $group = Group::query()->inRandomOrder()->first();

        // Then, we'll find a random user from that group.
        $from = $group->members()->inRandomOrder()->first();

        return [
            'group_id' => $group->id,
            'title' => $this->faker->sentence,
            'date' => $this->faker->date,
            'from' => $from->id,
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'desc' => $this->faker->paragraph,
        ];



    }
}




