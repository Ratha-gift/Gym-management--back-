<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPackage extends Model
{
    protected $primaryKey = 'package_id';

    protected $fillable = [
        'package_name',
        'duration_type',
        'duration_value',
        'price',
        'description',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'package_id', 'package_id');
    }
}
