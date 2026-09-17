<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Widens memberships.start_date/end_date from DATE to DATETIME so a
     * membership (a Day Pass especially) can start/end at a specific time,
     * not just midnight. Raw ALTER instead of Blueprint::change() — avoids
     * pulling in doctrine/dbal just for this one column-type change. MySQL
     * converts existing DATE values to DATETIME at midnight automatically,
     * so no existing membership data is lost or altered.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE memberships MODIFY start_date DATETIME NOT NULL');
        DB::statement('ALTER TABLE memberships MODIFY end_date DATETIME NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE memberships MODIFY start_date DATE NOT NULL');
        DB::statement('ALTER TABLE memberships MODIFY end_date DATE NOT NULL');
    }
};
