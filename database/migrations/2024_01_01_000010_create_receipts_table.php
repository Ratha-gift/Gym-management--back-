<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id('receipt_id');
            $table->foreignId('payment_id')->constrained('payments', 'payment_id')->cascadeOnDelete();
            $table->string('receipt_no', 50)->unique();
            $table->dateTime('receipt_date');
            $table->decimal('amount', 10, 2);
            $table->foreignId('received_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
