<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Audit logs are written by the application itself (see AuditLog::record())
 * and are read-only from the API — there is no store/update/destroy here.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest('created_at');

        if ($module = $request->query('module')) {
            $query->where('module', $module);
        }

        return $query->paginate((int) $request->query('per_page', 25));
    }

    public function show(AuditLog $auditLog)
    {
        return $auditLog->load('user');
    }
}
