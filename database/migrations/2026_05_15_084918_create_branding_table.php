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
        Schema::create('branding', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('Mi Bar');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->timestamps();
        });

        // Insert default row if none exists
        \DB::table('branding')->insertOrIgnore([
            'app_name' => 'Mi Bar',
            'logo_path' => null,
            'favicon_path' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branding');
    }
};
