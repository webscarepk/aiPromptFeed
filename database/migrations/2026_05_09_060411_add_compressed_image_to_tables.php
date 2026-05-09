<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('compressed_image')->nullable()->after('image');
        });
        Schema::table('types', function (Blueprint $table) {
            $table->string('compressed_image')->nullable()->after('image');
        });
        Schema::table('ai_prompts', function (Blueprint $table) {
            $table->string('compressed_image')->nullable()->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('compressed_image');
        });
        Schema::table('types', function (Blueprint $table) {
            $table->dropColumn('compressed_image');
        });
        Schema::table('ai_prompts', function (Blueprint $table) {
            $table->dropColumn('compressed_image');
        });
    }
};
