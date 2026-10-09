<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('type', ['bank', 'cash', 'ewallet', 'savings', 'other', 'credit'])->change();
            $table->decimal('credit_limit', 15, 2)->nullable()->after('current_balance');
            $table->tinyInteger('billing_day')->nullable()->after('credit_limit');
            $table->tinyInteger('due_day')->nullable()->after('billing_day');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'billing_day', 'due_day']);
            $table->enum('type', ['bank', 'cash', 'ewallet', 'savings', 'other'])->change();
        });
    }
};
