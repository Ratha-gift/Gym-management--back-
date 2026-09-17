<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * This backend is API-only (the login screen lives in the separate React
     * frontend) — there's no named 'login' web route to redirect to, so
     * always fall through to a plain 401 JSON response instead of Laravel's
     * default web-app redirect behavior.
     */
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }
}
