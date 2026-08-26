<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    protected $primaryKey = 'membership_id';

    protected $fillable = [
        'member_id',
        'package_id',
        'start_date',
        'end_date',
        'freeze_start',
        'freeze_end',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'freeze_start' => 'date',
        'freeze_end' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(MembershipPackage::class, 'package_id', 'package_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'membership_id', 'membership_id');
    }
}
