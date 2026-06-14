<?php

use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('permission_role') || ! Schema::hasTable('roles')) {
            return;
        }

        $now = now();
        $permissionsByKey = DB::table('permissions')->pluck('id', 'key');
        $rolesBySlug = DB::table('roles')->pluck('id', 'slug');

        foreach (PermissionCatalog::all() as $key => $permission) {
            if (! isset($permissionsByKey[$key])) {
                DB::table('permissions')->insert([
                    'key' => $key,
                    'group' => $permission['group'],
                    'name' => $permission['label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $permissionsByKey = DB::table('permissions')->pluck('id', 'key');

        if (isset($rolesBySlug['super_admin'])) {
            $this->syncRolePermissions($rolesBySlug['super_admin'], array_keys(PermissionCatalog::all()), $permissionsByKey, $now);
        }

        if (isset($rolesBySlug['manager'])) {
            $this->syncRolePermissions($rolesBySlug['manager'], PermissionCatalog::managerDefaults(), $permissionsByKey, $now);
        }

        if (isset($rolesBySlug['sales'])) {
            $this->syncRolePermissions($rolesBySlug['sales'], PermissionCatalog::salesDefaults(), $permissionsByKey, $now);
        }
    }

    public function down(): void
    {
        //
    }

    private function syncRolePermissions(int $roleId, array $keys, $permissionsByKey, $now): void
    {
        foreach ($keys as $key) {
            if (! isset($permissionsByKey[$key])) {
                continue;
            }

            DB::table('permission_role')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_id' => $permissionsByKey[$key],
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }
};
