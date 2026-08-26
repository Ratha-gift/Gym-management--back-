<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    const UPDATED_AT = null;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'membership_id',
        'member_id',
        'payment_date',
        'amount',
        'discount',
        'net_amount',
        'payment_method',
        'reference_no',
        'status',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'membership_id', 'membership_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PaymentDetail::class, 'payment_id', 'payment_id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class, 'payment_id', 'payment_id');
    }
}
