<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\Balance;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreExpenseRequest;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;
use phpseclib3\Crypt\RSA;





class ExpenseController extends Controller
{

        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except(
            'getExpensesForUserInGroup'
        );
    }










    /**
     * Update an expense.
     *
     * @param \Illuminate\Http\Request $request The request object.
     * @param \App\Models\Expense $expense The expense to be updated.
     * @return \Illuminate\Http\RedirectResponse The redirect response.
     */
    public function update(Request $request, Expense $expense)
    {


        // Check if the authenticated user is the creator of the expense
        if (auth()->id() !== $expense->from) {
            return redirect()->route('groups.show', ['group' => $expense->group_id])
                ->with('error', 'You are not authorized to update this expense.');
        }
        $validatedData = $request->validate([
            'title' =>'required|string|max:255',
            'desc' => 'nullable|string',
            'date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
        ]);

        $oldAmount = $expense->amount;
        //$expense->update($validatedData);

        $expense->fill($validatedData);
        $expense->save();
        $newAmount = $expense->amount;

        // If the amount is unchanged, return early
        if ($oldAmount == $newAmount) {
           // return redirect()->route('groups.show', ['group' => $expense->group_id]);
            return redirect()->route('groups.expenses', ['group' => $expense->group_id])->with('success', 'Expense updated successfully');
        }

        try {
            $members = $expense->members;
            $membersCount = $members->count();

            // Update the balance of each member
            foreach ($members as $member) {
                $balance = Balance::firstOrCreate([
                    'user_id' => $member->id,
                    'group_id' => $expense->group_id,
                ]);
                $difference = $oldAmount - $newAmount;

                if ($member->id == $expense->from) {
                    // The member who created the expense should receive less money back
                    $balance->balance -= ($difference / $membersCount) * ($membersCount - 1);
                } else {
                    // The other members should owe less money
                    $balance->balance += $difference / $membersCount;
                }

                $balance->save();
            }

            return redirect()->route('groups.expenses', ['group' => $expense->group_id])->with('success', 'Expense updated successfully');
            //return redirect()->route('groups.show', ['group' => $expense->group_id]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            session()->flash('error', 'An error occurred while updating the expense.');
            return redirect()->route('groups.show', ['group' => $expense->group_id]);
        }
    }




/**
 * Remove the specified resource from storage.
 * Only the user who created the expense can delete it.
 *
 * @param  string  $id
 * @return \Illuminate\Http\Response
 *
 */


public function destroy(string $id)
{
    $expense = Expense::findOrFail($id);

    if (auth()->user()->id == $expense->from) {
        $expense->delete();
        return response()->json(['success' => true]);
    } else {
        return response()->json(['error' => 'You are not authorized to delete this expense'], 403);
    }
}

   /* public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);

        if (auth()->user()->id == $expense->from) {
            $expense->delete();
            return redirect()->back()->with('success', 'Expense deleted successfully');
        } else {
            return redirect()->back()->with('error', 'You are not authorized to delete this expense');
        }
     }*/

    /**
     * Show the expenses of a group.
     * only the members of the group can see the expenses.
     */

    public function showGroupExpenses(Group $group)
    {
        // Check if the authenticated user is a member of the group
        $id=$group->id;
        if (!$group->members->contains(auth()->user())) {
            // If not, redirect them back with an error message
            return redirect()->back()->with('error', 'You are not a member of this group.');
        }

        // Fetch all expenses related to the group
       // $expenses = $group->expenses;
        $expenses = collect([]);

        // Return the expenses to a view
        return view('group_expenses', compact('expenses','id'));
    }








/**
 * Update the balance for a member after an expense is created.
 *
 * @param  Member  $member  The member for whom to update the balance.
 * @param  Expense  $expense  The expense for which the balance is being updated.
 * @param  int  $membersCount  The total number of members in the group.
 * @return void
 */
private function updateBalance($member, $expense, $amount,$membersCount)
{
    // Get the balance record for the member and the group
    $balance = Balance::firstOrCreate([
        'user_id' => $member->id,
        'group_id' => $expense->group_id,
    ]);

    if ($membersCount == 1) {
        // If there's only one user, increase their balance by the entire amount
        $balance->balance += $amount;
    } else {
        // Otherwise, distribute the amount among the users
        $amountPerMember = $amount / $membersCount;
        Log::info('amount: ' .$amountPerMember);//log the amount
        if ($member->id == $expense->from) {
            // If the member is the one who paid the expense, increase their balance
            $balance->balance += $amount - $amountPerMember;
        } else {
            // Otherwise, decrease their balance
            $balance->balance -= $amountPerMember;
        }
    }

    // Save the updated balance
    $balance->save();

    // Update the expense_user table
    //$expense->members()->attach($member->id);
}

/* public function store(StoreExpenseRequest $request)
{
    $expense = Expense::create($request->validated());

    try {
        $memberIds = $request->input('to');
        $members = User::whereIn('id', $memberIds)->get();
        Log::info('members: ' .$members);//log the members
        $membersCount = $members->count();
        Log::info('membersCount: ' .$membersCount);//log the members count

        // Update the balance of each member
        foreach ($members as $member) {
            $this->updateBalance($member, $expense, $membersCount);
        }

        return redirect()->route('groups.show', ['group' => $request->input('group_id')]);
    } catch (\Exception $e) {
        Log::error($e->getMessage());
        session()->flash('error', 'An error occurred while creating the expense.');
        Log::info('session:' . session()->all());
        return redirect()->route('groups.show', ['group' => $request->input('group_id')]);
    }
} */












public function store(StoreExpenseRequest $request)
{
    DB::beginTransaction();

    try {
        // Create a new expense
        $expense = Expense::create([
            'group_id' => $request->input('group_id'),
            'from' => $request->input('from'),
        ]);

        // Get the members
        $memberIds = $request->input('to');
        $members = User::whereIn('id', $memberIds)->get();
        $allmembers = Group::find($request->input('group_id'))->members;

        // Update the balance of each member and save a copy of the expense for each member
        foreach ($allmembers as $member) {
            //check if the member is in members
            if($members->contains($member)){
                $this->updateBalance($member, $expense,$request->input('amount'),$members->count());
            }
            // Convert the JWK to a PEM
            $jwkArray = json_decode($member->public_key, true);

            $rsa = PublicKeyLoader::load([
                'e' => new BigInteger(base64_decode(strtr($jwkArray['e'], '-_', '+/')), 256),
                'n' => new BigInteger(base64_decode(strtr($jwkArray['n'], '-_', '+/')), 256),
            ]);

            $pem = $rsa->toString('PKCS1');

            // Load the public key
            $publicKey = PublicKeyLoader::load($pem);



            // Encrypt the data with RSA-OAEP padding
            $encryptedTitle = $publicKey->encrypt($request->input('title'), RSA::ENCRYPTION_OAEP);
            $encryptedDate = $publicKey->encrypt($request->input('date'), RSA::ENCRYPTION_OAEP);
            $encryptedAmount = $publicKey->encrypt($request->input('amount'), RSA::ENCRYPTION_OAEP);
            $encryptedDesc = $publicKey->encrypt($request->input('desc'), RSA::ENCRYPTION_OAEP);

            // Base64 encode the encrypted data
            $encryptedTitle = base64_encode($encryptedTitle);
            $encryptedDate = base64_encode($encryptedDate);
            $encryptedAmount = base64_encode($encryptedAmount);
            $encryptedDesc = base64_encode($encryptedDesc);

            // Save a copy of the expense for the member
            $expense->users()->attach($member->id, [
                'title' => $encryptedTitle,
                'date' => $encryptedDate,
                'amount' => $encryptedAmount,
                'desc' => $encryptedDesc,
            ]);
        }

        DB::commit();

        return redirect()->route('groups.show', ['group' => $request->input('group_id')]);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error($e->getMessage());
        session()->flash('error', 'An error occurred while creating the expense.');

        return redirect()->route('groups.show', ['group' => $request->input('group_id')]);
    }
}

public function getExpensesForUserInGroup($userId, $groupId)
{
        $expenses = DB::table('expenses')
        ->join('user_expenses', 'expenses.id', '=', 'user_expenses.expense_id')
        ->where('expenses.group_id', $groupId)
        ->where('user_expenses.user_id', $userId)
        ->select('user_expenses.title', 'user_expenses.date', 'user_expenses.amount', 'user_expenses.desc', 'expenses.from', 'expenses.id as expense_id')
        ->get();

    foreach ($expenses as $expense) {
        $expense->title = mb_convert_encoding($expense->title, 'UTF-8', 'auto');
        $expense->date = mb_convert_encoding($expense->date, 'UTF-8', 'auto');
        $expense->amount = mb_convert_encoding($expense->amount, 'UTF-8', 'auto');
        $expense->desc = mb_convert_encoding($expense->desc, 'UTF-8', 'auto');
        $expense->from;
        $expense->expense_id;
    }

    return response()->json($expenses);
}



public function storeok(StoreExpenseRequest $request)
{
    DB::beginTransaction();

    try {
        // Create a new expense
        $expense = Expense::create([
            'group_id' => $request->input('group_id'),
            'from' => $request->input('from'),
        ]);

        // Get the members
        $memberIds = $request->input('to');
        $members = User::whereIn('id', $memberIds)->get();
        //get all the members of the group
        $allmembers = Group::find($request->input('group_id'))->members;

        // Update the balance of each member and save a copy of the expense for each member
        foreach ($allmembers as $member) {
            //check if the member is in members
            if($members->contains($member)){
                $this->updateBalance($member, $expense,$request->input('amount'),$members->count());
            }


            // Convert the JWK to a PEM
            $jwkArray = json_decode($member->public_key, true);
            Log::info('jwkArray: ' .$jwkArray);//log the jwkArray

            $rsa = PublicKeyLoader::load([
                'e' => new BigInteger(base64_decode(strtr($jwkArray['e'], '-_', '+/')), 256),
                'n' => new BigInteger(base64_decode(strtr($jwkArray['n'], '-_', '+/')), 256),
            ]);

            $pem = $rsa->toString('PKCS1');

            // Encrypt the sensitive data with the member's public key
            openssl_public_encrypt($request->input('title'), $encryptedTitle, $pem);
            openssl_public_encrypt($request->input('date'), $encryptedDate, $pem);
            openssl_public_encrypt($request->input('amount'), $encryptedAmount, $pem);
            openssl_public_encrypt($request->input('desc'), $encryptedDesc, $pem);


            $encryptedTitle = base64_encode($encryptedTitle);
            $encryptedDate = base64_encode($encryptedDate);
            $encryptedAmount = base64_encode($encryptedAmount);
            $encryptedDesc = base64_encode($encryptedDesc);

            // Save a copy of the expense for the member
            $expense->users()->attach($member->id, [
                'title' => $encryptedTitle,
                'date' => $encryptedDate,
                'amount' => $encryptedAmount,
                'desc' => $encryptedDesc,
            ]);
        }

        DB::commit();

        return redirect()->route('groups.show', ['group' => $request->input('group_id')]);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error($e->getMessage());
        session()->flash('error', 'An error occurred while creating the expense.');

        return redirect()->route('groups.show', ['group' => $request->input('group_id')]);
    }
}



}
