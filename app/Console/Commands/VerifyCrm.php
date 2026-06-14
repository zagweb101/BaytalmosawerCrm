<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class VerifyCrm extends Command
{
    protected $signature = 'crm:verify';

    protected $description = 'Verify CRM is ready for production use';

    public function handle(): int
    {
        $errors = 0;

        $this->info('=== CRM Verification ===');

        foreach ([
            'users', 'roles', 'permissions', 'permission_role', 'companies', 'customers',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("Missing table: {$table}");
                $errors++;
            } else {
                $this->line("OK  table {$table}");
            }
        }

        if (Schema::hasColumn('users', 'is_super_admin')) {
            $this->line('OK  users.is_super_admin column');
        } else {
            $this->warn('WARN users.is_super_admin column missing');
        }

        if (Schema::hasColumn('users', 'avatar')) {
            $this->line('OK  users.avatar column');
        } else {
            $this->warn('WARN users.avatar column missing');
        }

        $admin = User::with('roleModel')->where('email', 'admin@crm.local')->first();
        if (! $admin) {
            $this->error('Missing admin@crm.local user');
            $errors++;
        } else {
            $super = $admin->isSuperAdmin() ? 'yes' : 'no';
            $slug = $admin->roleModel?->slug ?? 'none';
            $this->line("OK  admin role_id={$admin->role_id} slug={$slug} super={$super}");
            if (! $admin->canDo('users.manage')) {
                $this->error('Admin cannot access users.manage');
                $errors++;
            }
        }

        $manar = User::with('roleModel')->where('email', 'manar@crm.local')->first();
        if ($manar) {
            $slug = $manar->roleModel?->slug ?? 'none';
            $this->line("OK  manar role_id={$manar->role_id} slug={$slug}");
            if ($manar->canDo('users.manage')) {
                $this->warn('WARN manar has users.manage (expected sales only)');
            }
        }

        $permCount = Permission::count();
        $roleCount = Role::count();
        $this->line("OK  permissions={$permCount} roles={$roleCount}");

        if ($permCount < 10) {
            $this->error('Too few permissions — run migrations');
            $errors++;
        }

        if ($errors === 0) {
            $this->info('RESULT: PASS — CRM ready');

            return self::SUCCESS;
        }

        $this->error("RESULT: FAIL — {$errors} issue(s)");

        return self::FAILURE;
    }
}
