<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    /**
     * Test case for the store method of the GroupController.
     *
     * This test verifies that a group can be successfully created and stored in the database.
     * It performs the following steps:
     * 1. Creates a user using the User factory.
     * 2. Logs in as the created user.
     * 3. Defines the group data.
     * 4. Sends a POST request to the store route with the group data.
     * 5. Asserts that the group was created in the database.
     */
    public function test_store()
    {
        // Create a user
        $user = User::factory()->create();

        // Log in as the user
        $this->actingAs($user);

        // Define the group data
        $groupData = [
            'name' => 'EsiCospendTestGroup',
            // other group data...
        ];

        // Send a POST request to the store route
        $response = $this->post(route('groups.store'), $groupData);

        // Assert that the group was created in the database
        $this->assertDatabaseHas('groups', [
            'name' => 'EsiCospendTestGroup',
            'user_id' => $user->id,
            // other group data...
        ]);

    }

    /**
     * Test if the user is a member of the created group.
     *
     * @return void
     */
    public function test_user_is_member_of_created_group()
    {
        // Create a user
        $user = User::factory()->create();

        // Log in as the user
        $this->actingAs($user);

        // Define the group data
        $groupData = [
            'name' => 'EsiCospendCineGroup2',
            // other group data...
        ];

        // Send a POST request to the store route
        $this->post(route('groups.store'), $groupData);

        // Assert that the group was created
        $this->assertDatabaseHas('groups', ['name' => 'EsiCospendCineGroup2']);

        // Retrieve the created group
        $group = Group::where('name', 'EsiCospendCineGroup2')->first();
        
        // Assert that the user is a member of the created group
        $this->assertTrue($group->users->contains($user));
    }


    /**
     * Test the group size of a created group.
     *
     * This test case verifies that a group is created successfully and that the group size is accurate.
     * It performs the following steps:
     * 1. Creates a user.
     * 2. Logs in as the user.
     * 3. Defines the group data.
     * 4. Sends a POST request to the store route to create the group.
     * 5. Asserts that the group was created in the database.
     * 6. Retrieves the created group.
     * 7. Creates two additional users and adds them to the group.
     * 8. Asserts that the group has 3 members.
     * 9. Asserts that the user is a member of the created group.
     */
    public function test_group_size__of_created_group()
    {
        // Create a user
        $user = User::factory()->create();

        // Log in as the user
        $this->actingAs($user);

        // Define the group data
        $groupData = [
            'name' => 'EsiCospendBarGroup',
            // other group data...
        ];

        // Send a POST request to the store route
        $this->post(route('groups.store'), $groupData);

        // Assert that the group was created
        $this->assertDatabaseHas('groups', ['name' => 'EsiCospendBarGroup']);

        // Retrieve the created group
        $group = Group::where('name', 'EsiCospendBarGroup')->first();

        // Create two additional users and add them to the group
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $group->users()->attach([$user2->id, $user3->id]);

        // Assert that the group has 3 members
        $this->assertEquals(3, $group->users->count());

        // Assert that the user is a member of the created group
        $this->assertTrue($group->users->contains($user));
    }

    /**
     * Test case to verify that the group owner is the creator of the group.
     *
     * This test creates a user, logs in as the user, and then creates a group.
     * It then retrieves the created group and asserts that the user is the owner of the group.
     */
    public function test_group_owner_is_creator()
    {
        // Create a user
        $user = User::factory()->create();

        // Log in as the user
        $this->actingAs($user);

        // Define the group data
        $groupData = [
            'name' => 'EsiCospendOwnerTestGroup',
            // other group data...
        ];

        // Send a POST request to the store route
        $this->post(route('groups.store'), $groupData);

        // Retrieve the created group
        $group = Group::where('name', 'EsiCospendOwnerTestGroup')->first();

        // Assert that the user is the owner of the created group
        $this->assertEquals($user->id, $group->user_id);
    }


    /**
     * Test case to verify that only the owner can add users to a group.
     *
     * @return void
     */
    public function test_only_owner_can_add_users_to_group()
    {
        // Create two users
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        // Log in as the owner
        $this->actingAs($owner);

        // Create a group
        $group = Group::factory()->create(['name' => 'Test Group', 'user_id' => $owner->id]);

        // Try to add the other user to the group
        $response = $this->post(route('groups.addUser', $group), ['user_id' => $otherUser->id]);

        // Assert that the other user was added to the group
        $this->assertTrue($group->users->contains($otherUser));

        // Log out the owner and log in as the other user
        $this->actingAs($otherUser);

        // Try to add a new user to the group
        $newUser = User::factory()->create();
        $response = $this->post(route('groups.addUser', $group), ['user_id' => $newUser->id]);

        // Assert that the new user was not added to the group
        $this->assertFalse($group->users->contains($newUser));
    }

    /**
     * Test case to verify that only the owner can remove users from a group.
     */
    public function test_only_owner_can_remove_users_from_group()
    {
        // Create two users
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        // Log in as the owner
        $this->actingAs($owner);

        // Create a group
        $group = Group::factory()->create(['name' => 'Test Group', 'user_id' => $owner->id]);

        // Add the other user to the group
        $group->users()->attach($otherUser);

        // Try to remove the other user from the group
        $response = $this->post(route('groups.removeUser', $group), ['user_id' => $otherUser->id]);

        // Assert that the other user was removed from the group
        $this->assertFalse($group->users->contains($otherUser));

        // Log out the owner and log in as the other user
        $this->actingAs($otherUser);

        // Try to remove the owner from the group
        $response = $this->post(route('groups.removeUser', $group), ['user_id' => $owner->id]);

        // Assert that the owner was not removed from the group
        $this->assertTrue($group->users->contains($owner));
    }
}





