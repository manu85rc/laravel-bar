<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branding', function (Blueprint $table) {
            $table->string('social_facebook', 255)->nullable()->after('phone');
            $table->string('social_whatsapp', 255)->nullable()->after('social_facebook');
            $table->string('social_tiktok', 255)->nullable()->after('social_whatsapp');
            $table->string('social_twitter', 255)->nullable()->after('social_tiktok');
            $table->string('social_instagram', 255)->nullable()->after('social_twitter');
        });
    }

    public function down(): void
    {
        Schema::table('branding', function (Blueprint $table) {
            $table->dropColumn(['social_facebook', 'social_whatsapp', 'social_tiktok', 'social_twitter', 'social_instagram']);
        });
    }
};
