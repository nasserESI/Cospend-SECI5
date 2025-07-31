<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GroupSeeder extends Seeder
{


    public function run(): void
    {
       /*  // Create 10 groups
        \App\Models\Group::factory(10)->create()->each(function ($group) {
            // For each group, create and associate 5 users
            $users = \App\Models\User::inRandomOrder()->limit(5)->get();
            $group->users()->attach($users);
        }); */
        Group::factory()
        ->count(10) // replace with the number of groups you want to create
        ->create();

    }
}
