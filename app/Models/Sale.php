<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'date', 'user_id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'sales_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
