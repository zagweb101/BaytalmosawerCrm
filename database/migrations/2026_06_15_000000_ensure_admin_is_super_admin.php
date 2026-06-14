<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $superAdminRoleId = Role::where('slug', 'super_admin')->value('id');

        if (! $superAdminRoleId) {
            return;
        }

        DB::table('users')
            ->where('email', 'admin@crm.local')
            ->update([
                'role_id' => $superAdminRoleId,
                'role' => 'manager',
                'is_super_admin' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $managerRoleId = Role::where('slug', 'manager')->value('id');

        if (! $managerRoleId) {
            return;
        }

        DB::table('users')
            ->where('email', 'admin@crm.local')
            ->update([
                'role_id' => $managerRoleId,
                'role' => 'manager',
                'is_super_admin' => false,
                'updated_at' => now(),
            ]);
    }
};
