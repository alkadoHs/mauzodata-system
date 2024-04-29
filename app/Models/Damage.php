<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Damage extends Model
{
    use HasFactory;

     protected $fillable = ['team_id', 'product_id', 'stock'];


    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
