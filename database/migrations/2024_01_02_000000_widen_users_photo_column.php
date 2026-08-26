<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widens users.photo from VARCHAR(255) to LONGTEXT so it can hold a base64
 * data URI for an uploaded profile picture (no separate file storage/disk
 * needed for this). Uses raw SQL instead of Schema::table()->change() so we
 * don't need to pull in doctrine/dbal just for this one column.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY photo LONGTEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY photo VARCHAR(255) NULL');
    }
};
