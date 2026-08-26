<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $primaryKey = 'member_id';

    protected $fillable = [
        'member_code',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'photo',
        'qr_code',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected $appends = ['name', 'membership_status'];

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /** The member's most recent membership record (by end date), regardless of status. */
    public function latestMembership()
    {
        return $this->hasOne(Membership::class, 'member_id', 'member_id')->latestOfMany('end_date');
    }

    public function getMembershipStatusAttribute(): string
    {
        return $this->latestMembership?->status ?? 'none';
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'member_id', 'member_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'member_id', 'member_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'member_id', 'member_id');
    }

    /** The member's current (latest) membership, if any. */
    public function activeMembership()
    {
        return $this->hasOne(Membership::class, 'member_id', 'member_id')
            ->where('status', 'active')
            ->latestOfMany('end_date');
    }

    /** The member's currently open attendance session (checked in, not yet checked out), if any. */
    public function openAttendance()
    {
        return $this->hasOne(Attendance::class, 'member_id', 'member_id')
            ->whereNull('check_out')
            ->latestOfMany('check_in');
    }
}
