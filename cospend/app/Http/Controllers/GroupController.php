<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreGroupRequest;

class GroupController extends Controller
{

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
     * Store a newly created resource in storage.
     */

     public function store(StoreGroupRequest $request)
     {
         $group = new Group($request->validated());
         $group->user_id = auth()->id();
         $group->save();
         $group->members()->attach(auth()->id());
 
         // Get all members of the group
         $members = Group::getAllMembers($group->id);
 
         // Create a balance for each member
         foreach ($members as $member) {
             $balance = new Balance;
             $balance->group_id = $group->id;
             $balance->user_id = $member->id; // Assuming the member object has an id property
             $balance->save();
         }
         return redirect('/home');

     }
 
   
    /**
     * Display the specified resource.
     * @param string $id the group id
     */
    public function show(string $id)
    {
        $members=Group::getAllMembers($id);
        $user_id=Group::find($id)->user_id;
        $group_name=Group::find($id)->name;
        return view('group_details',compact('members','id','user_id','group_name'));
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        // Check if the authenticated user is the owner of the group
        if (auth()->id() == $group->user_id) {
            // Check if the group has only the owner as a member
            if ($group->members()->count() == 1 && $group->members->first()->id == $group->user_id) {
                //detach the only member of the group aka the owner
                $group->members()->detach($group->user_id);
                //it might be necessary to check if the user balance is 0 in the future for the owner
                $group->balances()->delete();
                $group->delete();
                return redirect()->back()->with('success', 'Group deleted successfully.');
            } else {
                return redirect()->route('groups.show', ['group' => $group->id])
                    ->with('error', 'The group cannot be deleted as there are still members other than the owner.');
            }
        } else {
            return redirect()->route('groups.show', ['group' => $group->id])
                ->with('error', 'You do not have permission to delete this group.');
        }
    }
    



    /**
     * Add a user to the group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addUser(Request $request, Group $group)
    {
        // Check if the authenticated user is the owner of the group
        if (auth()->id() == $group->user_id) {
            
            if(Group::existsByEmail($request->input('email'))){
                $user = User::where('email', $request->input('email'))->first();
                $group->members()->attach($user);
                $balance = new Balance;
                $balance->group_id = $group->id;
                $balance->user_id = $user->id; 
                $balance->save();
            }
            else{
                session()->flash('error', 'The email you entered an invalid email.');
            }
            return redirect()->route('groups.show', ['group' => $group->id]);
        }
        
    }
    
    /**
     * Remove a user from a group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeUser(Request $request, Group $group, User $user)
    {
        // Check if the authenticated user is the owner of the group
        if (auth()->id() == $group->user_id) {
            $user = User::find($user->id);
            //it might be necessary to check if the user balance is 0 in the future
            $group->members()->detach($user);
            return redirect()->route('groups.show', ['group' => $group->id]);
        }
    }

}
