<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_prompts', function (Blueprint $table) {
            // JSON array of all uploaded image paths (original + compressed pairs)
            // e.g. [{"original":"prompts/abc.jpg","compressed":"prompts/abc_compressed.webp"}, ...]
            $table->json('images_data')->nullable()->after('compressed_image');
        });
    }

    public function down(): void
    {
        Schema::table('ai_prompts', function (Blueprint $table) {
            $table->dropColumn('images_data');
        });
    }
};
