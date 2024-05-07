<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'price', 'quantity'];

    protected $with = ['product'];

    protected $appends = ['total_price', 'profit'];


    protected function totalPrice(): Attribute
    {
        return new Attribute(
            get: fn (mixed $value, array $attributes) => $attributes['price'] * $attributes['quantity']
        );
    }

    protected function profit(): Attribute
    {
        return new Attribute(
            get: fn (mixed $value, array $attributes) => ($attributes['price'] - $this->product->buy_price) * $attributes['quantity']
        );
    }


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
