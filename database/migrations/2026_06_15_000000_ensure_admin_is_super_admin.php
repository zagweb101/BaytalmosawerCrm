<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'role_id')) {
                    $table->boolean('is_super_admin')->default(false)->after('role_id');
                } else {
                    $table->boolean('is_super_admin')->default(false)->after('role');
                }
            });
        }

        $superAdminRoleId = Role::where('slug', 'super_admin')->value('id');

        if (! $superAdminRoleId) {
            return;
        }

        $update = [
            'role_id' => $superAdminRoleId,
            'role' => 'manager',
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'is_super_admin')) {
            $update['is_super_admin'] = true;
        }

        DB::table('users')
            ->where('email', 'admin@crm.local')
            ->update($update);
    }

    public function down(): void
    {
        $managerRoleId = Role::where('slug', 'manager')->value('id');

        if (! $managerRoleId) {
            return;
        }

        $update = [
            'role_id' => $managerRoleId,
            'role' => 'manager',
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'is_super_admin')) {
            $update['is_super_admin'] = false;
        }

        DB::table('users')
            ->where('email', 'admin@crm.local')
            ->update($update);
    }
};
