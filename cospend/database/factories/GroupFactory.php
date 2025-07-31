<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Group;
use App\Models\Balance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'user_id' => \App\Models\User::all()->random()->id,
        ];
    }




/*     public function configure()
    {
        return $this->afterCreating(function (Group $group) {
            // Attach users to the group after it is created
            $users = \App\Models\User::inRandomOrder()->limit(5)->get();
            $group->users()->attach($users);

            // Create a balance for each member of the group
            foreach ($group->users as $user) {
                \App\Models\Balance::factory()->create([
                    'user_id' => $user->id,
                    'group_id' => $group->id,
                ]);
            }
        });
    } */

//a tester avec les nouvelles migrations
/*     public function configure()
{
    return $this->afterCreating(function (Group $group) {
        // Add the user specified in the 'user_id' attribute as a member of the group
        $group->users()->attach($group->user_id);

        // Create a balance for the user
        \App\Models\Balance::factory()->create([
            'user_id' => $group->user_id,
            'group_id' => $group->id,
        ]);

        // Attach additional random users to the group
        $users = \App\Models\User::inRandomOrder()->limit(4)->get();
        $group->users()->attach($users);

        // Create a balance for each additional member of the group
        foreach ($users as $user) {
            \App\Models\Balance::factory()->create([
                'user_id' => $user->id,
                'group_id' => $group->id,
            ]);
        }
    });
} */

public function configure()
{
    return $this->afterCreating(function (Group $group) {
        // Add the user specified in the 'user_id' attribute as a member of the group
        $group->users()->attach($group->user_id);

        // Create a balance for the user
        \App\Models\Balance::factory()->create([
            'user_id' => $group->user_id,
            'group_id' => $group->id,
        ]);

        // Attach additional random users to the group, excluding the owner
        $users = \App\Models\User::where('id', '!=', $group->user_id)->inRandomOrder()->limit(4)->get();
        $group->users()->attach($users);

        // Create a balance for each additional member of the group
        foreach ($users as $user) {
            \App\Models\Balance::factory()->create([
                'user_id' => $user->id,
                'group_id' => $group->id,
            ]);
        }
    });
}

}