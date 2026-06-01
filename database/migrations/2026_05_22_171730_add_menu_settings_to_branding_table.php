<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branding', function (Blueprint $table) {
            $table->string('font_family')->nullable()->after('favicon_path');
            $table->string('primary_color', 20)->nullable()->after('font_family');
            $table->string('background_color', 20)->nullable()->after('primary_color');
            $table->string('background_image_path')->nullable()->after('background_color');
        });
    }

    public function down(): void
    {
        Schema::table('branding', function (Blueprint $table) {
            $table->dropColumn(['font_family', 'primary_color', 'background_color', 'background_image_path']);
        });
    }
};
