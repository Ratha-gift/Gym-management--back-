<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id('membership_id');
            $table->foreignId('member_id')->constrained('members', 'member_id')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('membership_packages', 'package_id')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('freeze_start')->nullable();
            $table->date('freeze_end')->nullable();
            $table->enum('status', ['active', 'frozen', 'expired', 'terminated'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
