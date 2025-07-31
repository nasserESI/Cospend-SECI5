<?php

namespace Database\Seeders;

use App\Models\Expense;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Expense::factory()
        ->count(50)
        ->create()
        ->each(function (Expense $expense) {
            // Get all users in the same group as the expense
            $groupMembers = $expense->group->members;

            // Attach a random subset of these users to the expense
            $expense->members()->attach(
                $groupMembers->random(rand(1, $groupMembers->count()))->pluck('id')->toArray()
            );
        });

}
    
}




