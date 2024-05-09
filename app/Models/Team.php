<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'address', 'logo_url'];


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }


    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }


    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }


    public function creditSales(): HasMany
    {
        return $this->hasMany(CreditSale::class);
    }


    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }


    public function newStocks(): HasMany
    {
        return $this->hasMany(NewStock::class);
    }


    public function damages(): HasMany
    {
        return $this->hasMany(Damage::class);
    }
}
