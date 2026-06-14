<?php
/**
 * Bayt CRM — full health check (one script to see real problems)
 *
 * Run on server:
 *   /opt/alt/php82/usr/bin/php crm-health-check.php
 *
 * Saves report to: storage/logs/health-check-YYYYMMDD-HHMMSS.txt
 */

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$php = '/opt/alt/php82/usr/bin/php';
if (! is_file($php)) {
    $php = PHP_BINARY;
}

$issues = [];
$warnings = [];
$passed = 0;
$lines = [];

function out(string $msg): void
{
    global $lines;
    echo $msg."\n";
    $lines[] = $msg;
}

function pass(string $msg): void
{
    global $passed;
    $passed++;
    out("  OK   {$msg}");
}

function issue(string $msg): void
{
    global $issues;
    $issues[] = $msg;
    out("  FAIL {$msg}");
}

function warn(string $msg): void
{
    global $warnings;
    $warnings[] = $msg;
    out("  WARN {$msg}");
}

function tryCheck(string $label, callable $fn): void
{
    try {
        $fn();
        pass($label);
    } catch (Throwable $e) {
        issue("{$label} — ".$e->getMessage());
    }
}

function phpLint(string $phpBin, string $file): bool
{
    $out = [];
    $code = 0;
    exec($phpBin.' -l '.escapeshellarg($file).' 2>&1', $out, $code);

    return $code === 0;
}

out('========================================');
out('  Bayt CRM — Health Check');
out('  '.date('Y-m-d H:i:s'));
out('========================================');
out('');

// --- 1. Why things feel broken ---
out('[1] Context');
out('  Many fix-*.php / patch-*.php scripts were applied directly on the server.');
out('  This can leave mixed code, .bak files, and old errors in laravel.log.');
out('  This report shows CURRENT problems only.');
out('');

// --- 2. PHP syntax on critical files ---
out('[2] PHP syntax (critical files)');
$critical = [
    'app/Http/Controllers/UserController.php',
    'app/Models/User.php',
    'app/Models/Company.php',
    'app/Models/Customer.php',
    'app/Models/Role.php',
    'routes/web.php',
];

foreach ($critical as $rel) {
    $path = base_path($rel);
    if (! is_file($path)) {
        warn("missing: {$rel}");
        continue;
    }
    phpLint($php, $path) ? pass("syntax: {$rel}") : issue("syntax broken: {$rel}");
}
out('');

// --- 3. Known user-save bugs ---
out('[3] User save patch status');
$uc = is_file(base_path('app/Http/Controllers/UserController.php'))
    ? (file_get_contents(base_path('app/Http/Controllers/UserController.php')) ?: '')
    : '';

if ($uc === '') {
    issue('UserController.php missing');
} else {
    str_contains($uc, "\$data['is_super_admin']")
        ? issue('UserController still sets is_super_admin in withLegacyRole')
        : pass('UserController: no is_super_admin in withLegacyRole');
    str_contains($uc, 'onlyUserFields($this->withLegacyRole($data))')
        ? pass('UserController: onlyUserFields after withLegacyRole')
        : warn('UserController: field order may be wrong');
    str_contains($uc, '$user->companies()->sync($companyIds)')
        ? warn('UserController: duplicate companies()->sync may exist')
        : pass('UserController: no duplicate sync');
}

$um = is_file(base_path('app/Models/User.php'))
    ? (file_get_contents(base_path('app/Models/User.php')) ?: '')
    : '';
if ($um !== '' && preg_match('/protected\s+\$fillable\s*=\s*\[([\s\S]*?)\];/', $um, $m)) {
    str_contains($m[1], 'is_super_admin')
        ? issue('User model: is_super_admin still in fillable')
        : pass('User model: fillable OK');
    phpLint($php, base_path('app/Models/User.php'))
        ? pass('User model: syntax OK')
        : issue('User model: syntax broken');
}
out('');

// --- 4. Database ---
out('[4] Database');
tryCheck('DB connection', fn () => DB::select('SELECT 1'));

$coreTables = ['users', 'roles', 'companies', 'company_user', 'customers', 'deals', 'campaigns'];
foreach ($coreTables as $t) {
    Schema::hasTable($t) ? pass("table: {$t}") : issue("missing table: {$t}");
}

$userCols = Schema::hasTable('users') ? Schema::getColumnListing('users') : [];
foreach (['id', 'name', 'email', 'password', 'role', 'role_id'] as $col) {
    in_array($col, $userCols, true) ? pass("users.{$col}") : issue("missing column users.{$col}");
}
in_array('is_super_admin', $userCols, true)
    ? warn('users.is_super_admin column exists (unexpected)')
    : pass('users has no is_super_admin column (expected)');

tryCheck('users count >= 1', fn () => assert(User::count() >= 1));
tryCheck('roles count >= 4', fn () => assert(Role::count() >= 4));
tryCheck('companies count >= 2', fn () => assert(Company::count() >= 2));

if (Schema::hasTable('customers')) {
    $custCount = DB::table('customers')->count();
    $custCount >= 1 ? pass("customers: {$custCount}") : warn('customers table empty');
}
out('');

// --- 5. Page queries (what the UI loads) ---
out('[5] Page load simulation');
tryCheck('Users list', fn () => User::with(['roleModel', 'companies'])->latest()->paginate(12));
tryCheck('User create (roles)', fn () => Role::orderBy('name')->get());
tryCheck('Companies list', fn () => Company::orderBy('id')->get());

if (class_exists(App\Models\Customer::class)) {
    tryCheck('Customers list', fn () => App\Models\Customer::latest()->paginate(20));
}
if (class_exists(App\Models\Deal::class)) {
    tryCheck('Deals list', fn () => App\Models\Deal::latest()->paginate(20));
}
out('');

// --- 6. User create end-to-end ---
out('[6] User create test');
$testEmail = 'health-'.time().'@crm.local';
try {
    $roleId = Role::where('slug', 'sales')->value('id');
    $user = User::create([
        'name' => 'Health Check',
        'email' => $testEmail,
        'password' => 'HealthTest@123',
        'role' => 'sales',
        'role_id' => $roleId,
    ]);
    if (method_exists($user, 'companies')) {
        $user->companies()->sync([Company::orderBy('id')->value('id')]);
    }
    pass("User::create OK (#{$user->id})");
    DB::table('company_user')->where('user_id', $user->id)->delete();
    $user->delete();
} catch (Throwable $e) {
    issue('User::create — '.$e->getMessage());
}
out('');

// --- 7. Blade views ---
out('[7] Customer views (common patch target)');
foreach ([
    'resources/views/customers/index.blade.php',
    'resources/views/customers/_form.blade.php',
    'resources/views/customers/show.blade.php',
] as $rel) {
    $path = base_path($rel);
    if (! is_file($path)) {
        warn("view missing: {$rel}");
        continue;
    }
    $content = file_get_contents($path) ?: '';
    if (preg_match('/@error\s*\([^)]+\)\s*@enderror/s', $content)) {
        issue("broken Blade @error in {$rel}");
    } elseif (str_contains($content, '@enderror') && substr_count($content, '@error') !== substr_count($content, '@enderror')) {
        warn("unbalanced @error/@enderror in {$rel}");
    } else {
        pass("view OK: {$rel}");
    }
}
out('');

// --- 8. Recent log errors (last 2 hours) ---
out('[8] Recent errors in laravel.log (last 2 hours)');
$logPath = storage_path('logs/laravel.log');
$cutoff = time() - 7200;
$recentErrors = [];

if (is_file($logPath)) {
    $content = file_get_contents($logPath) ?: '';
    if (preg_match_all('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] production\.(ERROR|CRITICAL): (.+?)(?=\n\[|\z)/s', $content, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $ts = strtotime($match[1]);
            if ($ts >= $cutoff) {
                $recentErrors[] = ['time' => $match[1], 'msg' => substr(trim($match[3]), 0, 200)];
            }
        }
    }
}

if ($recentErrors === []) {
    pass('no ERROR/CRITICAL in last 2 hours');
} else {
    warn(count($recentErrors).' error(s) in last 2 hours:');
    foreach (array_slice($recentErrors, -5) as $err) {
        out("    [{$err['time']}] {$err['msg']}");
    }
}

// Old is_super_admin noise
if (preg_match_all('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*is_super_admin/s', $content ?? '', $old)) {
    $lastOld = end($old[1]);
    if ($lastOld && strtotime($lastOld) < $cutoff) {
        out("  NOTE: is_super_admin errors in log are OLD (last: {$lastOld}) — not current.");
    }
}
out('');

// --- 9. Patch clutter ---
out('[9] Server patch clutter');
$baks = glob(base_path('app/**/*.bak*')) ?: [];
$baks = array_merge($baks, glob(base_path('app/**/*.bak-*')) ?: []);
$baks = array_merge($baks, glob(base_path('*.php')) ?: []);
$fixScripts = array_filter(glob(base_path('fix-*.php')) ?: []);
$patchScripts = array_filter(glob(base_path('patch-*.php')) ?: []);
$diagScripts = array_filter(glob(base_path('diagnose-*.php')) ?: []);

out('  Fix scripts on server: '.count($fixScripts));
out('  Patch scripts on server: '.count($patchScripts));
out('  Diagnose scripts on server: '.count($diagScripts));
count($fixScripts) > 5 ? warn('many fix scripts — sign of unstable patching') : pass('fix script count acceptable');
out('');

// --- 10. Cache ---
out('[10] Clear stale cache');
try {
    Artisan::call('optimize:clear');
    pass('optimize:clear');
} catch (Throwable $e) {
    warn('optimize:clear — '.$e->getMessage());
}
out('');

// --- Summary ---
out('========================================');
out('  SUMMARY');
out('========================================');
out('  Passed:   '.$passed);
out('  Failures: '.count($issues));
out('  Warnings: '.count($warnings));
out('');

if ($issues) {
    out('  CRITICAL (fix these):');
    foreach ($issues as $i => $msg) {
        out('    '.($i + 1).". {$msg}");
    }
    out('');
}

if ($warnings) {
    out('  WARNINGS:');
    foreach ($warnings as $i => $msg) {
        out('    '.($i + 1).". {$msg}");
    }
    out('');
}

if ($issues === []) {
    out('  Verdict: Core CRM looks HEALTHY on server.');
    out('  If browser still has problems, describe which PAGE fails.');
} else {
    out('  Verdict: Server has real issues — see FAIL lines above.');
}

$report = storage_path('logs/health-check-'.date('Ymd-His').'.txt');
file_put_contents($report, implode("\n", $lines)."\n");
out('');
out("Report saved: {$report}");
out('========================================');

exit($issues ? 1 : 0);
