<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('sales')->after('password');
        });

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@crm.local'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('admin12345'),
                'role' => 'manager',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'admin@crm.local')->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
