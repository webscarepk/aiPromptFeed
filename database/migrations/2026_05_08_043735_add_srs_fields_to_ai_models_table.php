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
        Schema::table('ai_models', function (Blueprint $table) {
            $table->string('api_endpoint')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->integer('cost_per_use')->default(1);
            $table->boolean('is_active')->default(true);
            $table->json('capabilities')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            $table->dropColumn([
                'api_endpoint',
                'webhook_secret',
                'cost_per_use',
                'is_active',
                'capabilities'
            ]);
        });
    }
};
