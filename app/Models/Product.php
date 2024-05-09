<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'title',
        'unit',
        'product_id',
        'buy_price',
        'sale_price',
        'stock',
        'stock_alert',
        'discount_stock',
        'discount_price',
        'expire_date',
    ];


    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function damages(): HasMany
    {
        return $this->hasMany(Damage::class);
    }

    public function newStocks(): HasMany
    {
        return $this->hasMany(NewStock::class);
    }
}
