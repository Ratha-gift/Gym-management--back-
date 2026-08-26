<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    const CREATED_AT = null;

    protected $primaryKey = 'setting_id';

    protected $fillable = ['setting_key', 'setting_value'];
}
