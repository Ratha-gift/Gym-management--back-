<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('membership_id')->nullable()->constrained('memberships', 'membership_id')->nullOnDelete();
            $table->foreignId('member_id')->constrained('members', 'member_id')->cascadeOnDelete();
            $table->dateTime('payment_date');
            $table->decimal('amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->enum('payment_method', ['Cash', 'Card', 'Bank Transfer', 'Other'])->default('Cash');
            $table->string('reference_no', 100)->nullable();
            $table->enum('status', ['paid', 'partial', 'refunded'])->default('paid');
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
