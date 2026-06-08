<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            // Track balance snapshot for ledger accuracy
            $table->integer('balance_before')->nullable()->after('description');
            $table->integer('balance_after')->nullable()->after('balance_before');
            // Optional: link to a generation job for refund traceability
            $table->unsignedBigInteger('generation_job_id')->nullable()->after('balance_after');
        });
    }

    public function down(): void
    {
        Schema::table('credit_histories', function (Blueprint $table) {
            $table->dropColumn(['balance_before', 'balance_after', 'generation_job_id']);
        });
    }
};
