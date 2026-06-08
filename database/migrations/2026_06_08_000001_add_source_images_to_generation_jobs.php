<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generation_jobs', function (Blueprint $table) {
            // Multi-image support (up to 5 images)
            $table->json('source_images_urls')->nullable()->after('source_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('generation_jobs', function (Blueprint $table) {
            $table->dropColumn('source_images_urls');
        });
    }
};
