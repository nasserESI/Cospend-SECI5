<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Retrieve the members of a group.
     *
     * @param int $groupId The ID of the group.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the group members.
     */
    public function members($groupId)
    {
        $members = Group::find($groupId)->users()->get();
        return response()->json($members);
    }

    /**
     * Retrieve the owner of a group.
     *
     * @param int $groupId The ID of the group.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the group owner.
     */


    public function owner($groupId)
    {
        $group = Group::find($groupId);
        $owner = $group->owner;
        return response()->json($owner);
    }


}
