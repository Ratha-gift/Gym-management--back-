<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    const UPDATED_AT = null;

    protected $primaryKey = 'sale_id';

    protected $fillable = [
        'sale_no',
        'sale_date',
        'total_amount',
        'discount',
        'net_amount',
        'payment_method',
        'received_amount',
        'change_amount',
        'created_by',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'sale_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
