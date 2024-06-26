<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'user_id', 'customer_id', 'payment_method_id', 'invoice_number', 'status'];


    protected $with = ['orderItems'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }


    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function creditSale(): HasOne
    {
        return $this->hasOne(CreditSale::class);
    }
}
