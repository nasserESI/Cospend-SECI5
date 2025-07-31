<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = [
        'name',
    ];


    use HasFactory;
    
    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function expenses() : HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }
    // app/Models/User.php and app/Models/Group.php

    public function balances()
    {
        return $this->hasMany(Balance::class);
    }
    /**
     * Retrieve all members of a group.
     *
     * @param int $groupId The ID of the group.
     * @return \Illuminate\Database\Eloquent\Collection The collection of group members.
     */
    public static function getAllMembers($groupId)
    {
        return Group::find($groupId)->users()->get();
    }
/**
 * Check if a user with the given email exists.
 *
 * @param string $email The email to check.
 * @return bool True if a user with the given email exists, false otherwise.
 */
public static function existsByEmail($email)
{
    return User::where('email', $email)->exists();
}


}
