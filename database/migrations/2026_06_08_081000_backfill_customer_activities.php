<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers') || ! Schema::hasTable('customer_activities')) {
            return;
        }

        DB::table('customers')
            ->select(['id', 'team_member_id', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunk(100, function ($customers): void {
                foreach ($customers as $customer) {
                    $hasActivity = DB::table('customer_activities')
                        ->where('customer_id', $customer->id)
                        ->exists();

                    if ($hasActivity) {
                        continue;
                    }

                    $timestamp = $customer->created_at ?? $customer->updated_at ?? now();

                    DB::table('customer_activities')->insert([
                        'customer_id' => $customer->id,
                        'team_member_id' => $customer->team_member_id,
                        'type' => 'created',
                        'title' => 'تم إضافة العميل',
                        'description' => 'تم إنشاء ملف العميل في النظام.',
                        'metadata' => null,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_activities')) {
            return;
        }

        DB::table('customer_activities')
            ->where('type', 'created')
            ->where('description', 'تم إنشاء ملف العميل في النظام.')
            ->delete();
    }
};
