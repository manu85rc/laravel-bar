<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('password');
        });

        $roles = DB::table('roles')->pluck('id', 'slug')->toArray();

        $users = DB::table('users')->select('id', 'role')->get();
        foreach ($users as $user) {
            $roleId = $roles[$user->role] ?? null;
            DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
        });

        $roles = DB::table('roles')->pluck('slug', 'id')->toArray();

        $users = DB::table('users')->select('id', 'role_id')->get();
        foreach ($users as $user) {
            $slug = $roles[$user->role_id] ?? 'user';
            DB::table('users')->where('id', $user->id)->update(['role' => $slug]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
