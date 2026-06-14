<?php

use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('company_user', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['company_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->boolean('is_super_admin')->default(false)->after('role_id');
        });

        $now = now();

        foreach (PermissionCatalog::all() as $key => $permission) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $key],
                [
                    'group' => $permission['group'],
                    'name' => $permission['label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $roles = [
            'super_admin' => ['name' => 'سوبر أدمن', 'description' => 'صلاحيات كاملة على كل الشركات وكل أجزاء النظام.'],
            'manager' => ['name' => 'مدير', 'description' => 'إدارة كاملة للنظام والعملاء والتقارير.'],
            'sales' => ['name' => 'متابعة ومبيعات', 'description' => 'متابعة العملاء والصفقات والمهام اليومية.'],
        ];

        foreach ($roles as $slug => $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'is_system' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $permissionsByKey = DB::table('permissions')->pluck('id', 'key');
        $rolesBySlug = DB::table('roles')->pluck('id', 'slug');

        $this->syncPermissions($rolesBySlug['super_admin'], array_keys(PermissionCatalog::all()), $permissionsByKey, $now);
        $this->syncPermissions($rolesBySlug['manager'], PermissionCatalog::managerDefaults(), $permissionsByKey, $now);
        $this->syncPermissions($rolesBySlug['sales'], PermissionCatalog::salesDefaults(), $permissionsByKey, $now);

        DB::table('users')->orderBy('id')->get()->each(function ($user) use ($rolesBySlug) {
            $legacyRole = $user->role ?: 'sales';
            $slug = $legacyRole === 'manager' ? 'manager' : 'sales';

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'role_id' => $rolesBySlug[$slug],
                    'is_super_admin' => $user->email === 'admin@crm.local',
                    'updated_at' => now(),
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('is_super_admin');
        });

        Schema::dropIfExists('company_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    private function syncPermissions(int $roleId, array $keys, $permissionsByKey, $now): void
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
