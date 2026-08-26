<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $primaryKey = 'log_id';

    protected $fillable = ['user_id', 'action', 'module', 'record_id', 'ip_address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /** Convenience helper for controllers to drop an audit trail entry. */
    public static function record(?int $userId, string $action, ?string $module = null, ?int $recordId = null): self
    {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'ip_address' => request()->ip(),
        ]);
    }
}
