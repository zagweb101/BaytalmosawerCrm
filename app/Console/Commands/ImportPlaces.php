<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportPlaces extends Command
{
    protected $signature = 'places:import {--file=storage/app/places-import.json}';

    protected $description = 'Import places from JSON into companies table';

    public function handle(): int
    {
        $path = base_path($this->option('file'));

        if (! File::exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $rows = json_decode(File::get($path), true);

        if (! is_array($rows)) {
            $this->error('Invalid JSON file.');

            return self::FAILURE;
        }

        $imported = 0;

        foreach ($rows as $row) {
            DB::table('companies')->updateOrInsert(
                ['name' => $row['name']],
                [
                    'category' => $row['category'] ?? null,
                    'activity' => $row['type'] ?? null,
                    'address' => $row['address'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'social_url' => $row['social_url'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $imported++;
        }

        $this->info("Imported {$imported} places.");

        return self::SUCCESS;
    }
}
