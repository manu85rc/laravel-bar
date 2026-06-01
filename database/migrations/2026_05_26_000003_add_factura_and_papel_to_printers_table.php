<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE printers MODIFY COLUMN tipo ENUM('cocina','bebidas','ambas','factura') NOT NULL DEFAULT 'ambas'");

        Schema::table('printers', function (Blueprint $table) {
            $table->enum('papel', ['58mm', '80mm'])->default('80mm')->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $table->dropColumn('papel');
        });

        DB::statement("ALTER TABLE printers MODIFY COLUMN tipo ENUM('cocina','bebidas','ambas') NOT NULL DEFAULT 'ambas'");
    }
};
