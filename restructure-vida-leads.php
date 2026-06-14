<?php
/**
 * Restructure CRM data:
 * - Keep only 2 companies: بيت المصور + فيدا برودكشن
 * - Import 80 Excel places as leads (customers) under فيدا برودكشن
 *
 * Run on server:
 * /opt/alt/php82/usr/bin/php restructure-vida-leads.php
 *
 * Dry-run (preview only):
 * /opt/alt/php82/usr/bin/php restructure-vida-leads.php --dry-run
 */

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$dryRun = in_array('--dry-run', $argv ?? [], true);
$jsonFile = __DIR__.'/storage/app/places-import.json';

const COMPANY_BAYT = 'بيت المصور';
const COMPANY_VIDA = 'فيدا برودكشن';

function step(string $msg): void
{
    echo "  → {$msg}\n";
}

function ok(string $msg): void
{
    echo "  OK  {$msg}\n";
}

function tableColumns(string $table): array
{
    return Schema::getColumnListing($table);
}

function fillColumns(array $cols, array $map): array
{
    $data = [];
    foreach ($map as $col => $val) {
        if (in_array($col, $cols, true)) {
            $data[$col] = $val;
        }
    }

    return $data;
}

function slugEmail(string $name, int $index): string
{
    $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $name) ?? '';
    $slug = trim($slug, '-');
    if ($slug === '') {
        $slug = 'ar';
    }

    return 'vida-lead-'.$index.'-'.$slug.'@crm.local';
}

function ensureCustomerColumns(): void
{
    $columns = [
        'category' => 'VARCHAR(255) NULL',
        'activity' => 'VARCHAR(255) NULL',
        'address' => 'TEXT NULL',
        'phone' => 'VARCHAR(30) NULL',
        'social_url' => 'TEXT NULL',
        'company_id' => 'BIGINT UNSIGNED NULL',
        'stage' => 'VARCHAR(50) NULL',
        'status' => 'VARCHAR(50) NULL',
        'source' => 'VARCHAR(100) NULL',
    ];

    foreach ($columns as $name => $definition) {
        if (! Schema::hasTable('customers') || Schema::hasColumn('customers', $name)) {
            continue;
        }
        DB::statement("ALTER TABLE customers ADD COLUMN {$name} {$definition}");
        ok("Added customers.{$name}");
    }
}

function upsertCompany(string $name, array $extra = []): int
{
    global $dryRun;

    $cols = tableColumns('companies');
    $existing = DB::table('companies')->where('name', $name)->value('id');

    if ($existing) {
        if (! $dryRun && $extra) {
            $update = fillColumns($cols, array_merge($extra, ['updated_at' => now()]));
            if ($update) {
                DB::table('companies')->where('id', $existing)->update($update);
            }
        }

        return (int) $existing;
    }

    $data = fillColumns($cols, array_merge([
        'name' => $name,
        'activity' => $extra['activity'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ], $extra));

    if ($dryRun) {
        return 0;
    }

    return (int) DB::table('companies')->insertGetId($data);
}

function loadLeadRows(string $jsonFile): array
{
    if (is_file($jsonFile)) {
        $rows = json_decode(file_get_contents($jsonFile), true);
        if (is_array($rows) && count($rows) > 0) {
            return $rows;
        }
    }

    // Fallback: migrate from misplaced companies rows
    $keep = [COMPANY_BAYT, COMPANY_VIDA];

    return DB::table('companies')
        ->whereNotIn('name', $keep)
        ->orderBy('id')
        ->get()
        ->map(fn ($row) => [
            'name' => $row->name,
            'category' => $row->category ?? null,
            'type' => $row->activity ?? null,
            'address' => $row->address ?? null,
            'phone' => $row->phone ?? null,
            'social_url' => $row->social_url ?? null,
        ])
        ->all();
}

echo "=== Restructure: Vida leads ===\n";
echo $dryRun ? "MODE: dry-run (no changes)\n" : "MODE: live\n";
echo date('Y-m-d H:i:s')."\n\n";

try {
    if (! Schema::hasTable('companies')) {
        throw new RuntimeException('companies table missing');
    }
    if (! Schema::hasTable('customers')) {
        throw new RuntimeException('customers table missing');
    }

    // --- 1. Ensure customer columns ---
    echo "[1] Prepare customers table\n";
    if (! $dryRun) {
        ensureCustomerColumns();
    }
    ok('customers table ready');

    // --- 2. Create the 2 real companies ---
    echo "\n[2] Setup companies (2 only)\n";
    $baytId = upsertCompany(COMPANY_BAYT, ['activity' => 'تصوير وإنتاج']);
    $vidaId = upsertCompany(COMPANY_VIDA, ['activity' => 'فيدا برودكشن']);
    ok(COMPANY_BAYT.($baytId ? " #{$baytId}" : ' (dry)'));
    ok(COMPANY_VIDA.($vidaId ? " #{$vidaId}" : ' (dry)'));

    if (! $dryRun && $vidaId === 0) {
        throw new RuntimeException('Failed to resolve فيدا برودكشن company id');
    }

    // --- 3. Import leads as customers ---
    echo "\n[3] Import leads under ".COMPANY_VIDA."\n";
    $leads = loadLeadRows($jsonFile);
    if ($leads === []) {
        throw new RuntimeException('No lead rows found in places-import.json or companies table');
    }

    $customerCols = tableColumns('customers');
    $imported = 0;
    $updated = 0;
    $index = 0;

    foreach ($leads as $row) {
        $index++;
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        $email = slugEmail($name, $index);
        $payload = fillColumns($customerCols, [
            'name' => $name,
            'email' => $email,
            'phone' => $row['phone'] ?? null,
            'category' => $row['category'] ?? null,
            'activity' => $row['type'] ?? ($row['activity'] ?? null),
            'address' => $row['address'] ?? null,
            'social_url' => $row['social_url'] ?? null,
            'company_id' => $vidaId,
            'stage' => 'lead',
            'status' => 'prospect',
            'source' => 'vida_excel_import',
            'updated_at' => now(),
        ]);

        if ($dryRun) {
            $imported++;
            continue;
        }

        $existingId = DB::table('customers')
            ->where('name', $name)
            ->when(
                in_array('company_id', $customerCols, true) && $vidaId > 0,
                fn ($q) => $q->where('company_id', $vidaId)
            )
            ->value('id');

        if ($existingId) {
            DB::table('customers')->where('id', $existingId)->update($payload);
            $updated++;
        } else {
            $payload['created_at'] = now();
            DB::table('customers')->insert($payload);
            $imported++;
        }
    }

    ok("Leads processed: {$imported} new, {$updated} updated (total rows: ".count($leads).')');

    // --- 4. Remove misplaced company rows ---
    echo "\n[4] Clean companies table\n";
    $keepIds = array_filter([$baytId, $vidaId]);
    $oldCompanies = DB::table('companies')
        ->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds))
        ->pluck('id', 'name');

    $removeCount = $oldCompanies->count();
    step("Old company rows to remove: {$removeCount}");

    if (! $dryRun && $removeCount > 0) {
        $oldIds = $oldCompanies->values()->all();

        if (Schema::hasTable('company_user')) {
            DB::table('company_user')->whereIn('company_id', $oldIds)->delete();
            ok('Removed company_user pivots');
        }

        if (Schema::hasTable('campaigns') && Schema::hasColumn('campaigns', 'company_id')) {
            $campaignCount = DB::table('campaigns')->whereIn('company_id', $oldIds)->count();
            if ($campaignCount > 0) {
                step("Warning: {$campaignCount} campaigns still linked to old companies — not deleted");
            }
        }

        DB::table('companies')->whereIn('id', $oldIds)->delete();
        ok("Deleted {$removeCount} old company rows");
    } elseif ($dryRun) {
        ok("Would delete {$removeCount} company rows");
    } else {
        ok('No old company rows to delete');
    }

    // --- 5. Summary ---
    echo "\n[5] Summary\n";
    if (! $dryRun) {
        ok('companies: '.DB::table('companies')->count());
        ok('customers: '.DB::table('customers')->count());
        if (Schema::hasColumn('customers', 'company_id') && $vidaId > 0) {
            ok('vida leads: '.DB::table('customers')->where('company_id', $vidaId)->count());
        }
        if (Schema::hasColumn('customers', 'category')) {
            foreach (DB::table('customers')->select('category', DB::raw('COUNT(*) as n'))->groupBy('category')->orderByDesc('n')->get() as $r) {
                ok('  category '.($r->category ?: '—').': '.$r->n);
            }
        }
    }

    echo "\n=== Done! ===\n";
    echo "Open CRM → العملاء (customers) to see Vida leads.\n";
    echo "Companies should show only: ".COMPANY_BAYT." + ".COMPANY_VIDA."\n";

    if ($dryRun) {
        echo "\nRe-run without --dry-run to apply changes.\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
    exit(1);
}
