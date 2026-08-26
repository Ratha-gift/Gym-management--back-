<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'receipt_id';

    protected $fillable = ['payment_id', 'receipt_no', 'receipt_date', 'amount', 'received_by', 'notes'];

    protected $casts = [
        'receipt_date' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'user_id');
    }
}
