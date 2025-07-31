<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BalanceController extends Controller
{



    
    /**
     * Show the balance for a specific group.
     * Used for testing purposes only.
     * @param Group $group The group for which to show the balance.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the balances of the group members.
     */
    public function showBalance(Group $group)
    {
        $user = auth()->user();

        // Check if the user is a member of the group
        if ($group->users->contains($user)) {
            // Get all the users of the group
            $users = $group->users;
            $balances = [];

            // Loop through the users and get the balance related to that group for each user and store it in an array
            foreach ($users as $user) {
                $balance = $user->balances->where('group_id', $group->id)->first();
                $balances[$user->name] = $balance->balance;
            }


            // Return the array
            return response()->json($balances, 200);
        } else {
            return response()->json(['message' => 'You are not a member of this group'], 403);
        }
    }
}
