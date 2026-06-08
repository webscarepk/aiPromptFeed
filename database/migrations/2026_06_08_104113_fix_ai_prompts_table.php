<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_prompts', function (Blueprint $table) {
            // Add compressed_image column if it doesn't exist
            if (!Schema::hasColumn('ai_prompts', 'compressed_image')) {
                $table->string('compressed_image')->nullable()->after('image');
            }

            // Make ai_model_id nullable so the web form doesn't need it
            if (Schema::hasColumn('ai_prompts', 'ai_model_id')) {
                $table->foreignId('ai_model_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_prompts', function (Blueprint $table) {
            if (Schema::hasColumn('ai_prompts', 'compressed_image')) {
                $table->dropColumn('compressed_image');
            }
        });
    }
};
