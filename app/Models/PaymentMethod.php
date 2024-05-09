<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'name'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }


    public function orders(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creditSalePayments(): HasMany
    {
        return $this->hasMany(CreditSalePayment::class);
    }


    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
