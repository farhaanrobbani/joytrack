<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            // vehicle linkage - nullable, no FK constraint until vehicle tables exist
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('fuel_record_id')->nullable();
            $table->unsignedBigInteger('service_record_id')->nullable();
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            // transfer specifics
            $table->uuid('transfer_group_id')->nullable();
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
            $table->index(['account_id']);
            $table->index(['category_id']);
            $table->index(['vehicle_id']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
