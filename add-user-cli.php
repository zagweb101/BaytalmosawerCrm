<?php
/**
 * Add a user with role via CLI (when UI fails).
 * Usage:
 * /opt/alt/php82/usr/bin/php add-user-cli.php "الاسم" "email@crm.local" "password" "manager"
 */

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$name = $argv[1] ?? 'مستخدم جديد';
$email = $argv[2] ?? 'user@crm.local';
$password = $argv[3] ?? 'User@123456';
$role = $argv[4] ?? 'manager';

$allowed = ['manager', 'sales', 'admin', 'viewer'];
if (! in_array($role, $allowed, true)) {
    echo "Role must be one of: ".implode(', ', $allowed)."\n";
    exit(1);
}

$user = User::updateOrCreate(
    ['email' => $email],
    [
        'name' => $name,
        'password' => $password,
        'role' => $role,
        'email_verified_at' => now(),
    ]
);

echo "User saved:\n";
echo "  Name: {$user->name}\n";
echo "  Email: {$email}\n";
echo "  Password: {$password}\n";
echo "  Role: {$role}\n";
