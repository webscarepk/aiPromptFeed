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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });

        Schema::table('user_credit_balances', function (Blueprint $table) {
            $table->dropColumn('reset_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('billing_cycle')->default('monthly');
        });

        Schema::table('user_credit_balances', function (Blueprint $table) {
            $table->timestamp('reset_at')->nullable();
        });
    }
};
