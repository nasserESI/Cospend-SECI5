<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\Balance;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BalanceControllerTest extends TestCase
{
    use RefreshDatabase;


    public function test_member_Group_Creator_can_view_balances_of_group(): void
    {
        // Create a group and two users
        $user1 = User::factory()->create();
    
        $group = Group::factory()->create(['user_id' => $user1->id, 'name' => 'EsiCospendTestGroup']  );
        $user2 = User::factory()->create();
        $group->users()->attach([$user2->id]);

        // assert group has 2 members
        $this->assertEquals(2, $group->users()->count());

        // Create a balance for the second user
        $balance2 = Balance::factory()->create(['user_id' => $user2->id, 'group_id' => $group->id]);

        // Act as user1 and make a request to the showBalance method
        $response = $this->actingAs($user1)->get(route('groups.balances', $group));

        // Assert that the response status is 200
        $response->assertStatus(200);

        // Assert that the response data contains the balances of all members in the group and that the balance of all members is 
        $response->assertJson([
            $user1->name => 0,
            $user2->name => 0,
        ]);
    }

    public function test_non_member_cannot_view_balances_of_group(): void
    {

        $user1 = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $user1->id, 'name' => 'EsiCospendTestGroup']  );
        $user2 = User::factory()->create();

        // assert group has 2 members
        $this->assertEquals(1, $group->users()->count());


        // Act as the user and make a request to the showBalance method
        $response = $this->actingAs($user2)->get(route('groups.balances', $group));

        // Assert that the response status is 403
        $response->assertStatus(403);

        // Assert that the response data contains the error message
        $response->assertJson(['message' => 'You are not a member of this group']);
    }

    public function test_member_can_view_balances_of_group(): void
    {
        // Create a group and two users
        $user1 = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $user1->id, 'name' => 'EsiCospendTestGroup']);
        $user2 = User::factory()->create();
        $group->users()->attach([$user2->id]);

        // assert group has 2 members
        $this->assertEquals(2, $group->users()->count());

        // Create a balance for the second user
        $balance2 = Balance::factory()->create(['user_id' => $user2->id, 'group_id' => $group->id]);

        // Act as user2 and make a request to the showBalance method
        $response = $this->actingAs($user2)->get(route('groups.balances', $group));

        // Assert that the response status is 200
        $response->assertStatus(200);

        // Assert that the response data contains the balances of all members in the group
        $response->assertJson([
            $user1->name => 0,
            $user2->name => 0,
        ]);
    }

}
