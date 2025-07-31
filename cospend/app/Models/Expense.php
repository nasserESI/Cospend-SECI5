<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Expense extends Model
{
    use HasFactory;


    //protected $fillable = ['group_id', 'title', 'date', 'from', 'amount', 'desc'];
    protected $fillable = ['group_id','from'];

/*     public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from');
    } */

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }


/*     public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'expense_user', 'expense_id', 'user_id');
    } */

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_expenses')
                    ->withPivot('title', 'date', 'amount', 'desc')
                    ->withTimestamps();
    }

}
