<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('roles')) {
            return;
        }

        $rolesBySlug = DB::table('roles')->pluck('id', 'slug');

        DB::table('users')->orderBy('id')->get()->each(function ($user) use ($rolesBySlug) {
            if ($user->role_id) {
                return;
            }

            $legacyRole = $user->role ?: 'sales';
            $slug = in_array($legacyRole, ['manager', 'admin'], true) ? 'manager' : 'sales';

            if (! isset($rolesBySlug[$slug])) {
                return;
            }

            DB::table('users')->where('id', $user->id)->update([
                'role_id' => $rolesBySlug[$slug],
                'role' => $slug === 'manager' ? 'manager' : 'sales',
                'updated_at' => now(),
            ]);
        });

        if (! isset($rolesBySlug['sales'])) {
            return;
        }

        $manarUpdate = [
            'role_id' => $rolesBySlug['sales'],
            'role' => 'sales',
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'is_super_admin')) {
            $manarUpdate['is_super_admin'] = false;
        }

        DB::table('users')
            ->where('email', 'manar@crm.local')
            ->update($manarUpdate);
    }

    public function down(): void
    {
        //
    }
};
