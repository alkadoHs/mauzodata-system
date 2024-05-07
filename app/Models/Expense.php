<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'team_id'];


    protected $with = ['expenseItems'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }


    public function expenseItems(): HasMany
    {
        return $this->hasMany(ExpenseItem::class);
    }
    
}
