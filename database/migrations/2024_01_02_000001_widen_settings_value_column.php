<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widens settings.setting_value from TEXT (~64KB) to LONGTEXT so a setting
 * like a base64-encoded gym logo data URI doesn't get silently truncated —
 * same reasoning as widen_users_photo_column.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE settings MODIFY setting_value LONGTEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE settings MODIFY setting_value TEXT NULL');
    }
};
