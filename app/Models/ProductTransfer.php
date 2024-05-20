<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'to',
        'to_team_id',
        'to_user_id',
        'product_id',
        'stock',
        'status',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function toTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'to_team_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
