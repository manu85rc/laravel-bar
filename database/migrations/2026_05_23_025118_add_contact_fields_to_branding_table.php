<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branding', function (Blueprint $table) {
            $table->string('tagline', 255)->nullable()->after('app_name');
            $table->string('address', 255)->nullable()->after('background_image_path');
            $table->string('phone', 50)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('branding', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'address', 'phone']);
        });
    }
};
