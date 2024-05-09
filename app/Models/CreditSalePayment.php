<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditSalePayment extends Model
{
    use HasFactory;

    protected $fillable = ['credit_sale_id', 'user_id', 'payment_method_id', 'paid'];

    public function creditSale():BelongsTo
    {
        return $this->belongsTo(CreditSale::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
