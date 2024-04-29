<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditSale extends Model
{
    use HasFactory;

    protected $fillable = ['team_id' ,'order_id', 'user_id'];


    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function creditSalePayments(): HasMany
    {
        return $this->hasMany(CreditSalePayment::class);
    }
}
