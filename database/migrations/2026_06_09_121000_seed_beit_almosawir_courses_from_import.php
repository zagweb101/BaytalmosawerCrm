<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $beitId = DB::table('companies')->where('name', 'بيت المصور')->value('id');

        if (! $beitId) {
            return;
        }

        $courses = [
            'اساسيات التصوير',
            'احتراف الاضاءة',
            'احتراف تصوير الاعراس',
            'احتراف تصوير الاطعمة',
            'تصوير بالجوال',
            'فاشن',
        ];

        foreach ($courses as $course) {
            DB::table('company_offerings')->updateOrInsert(
                [
                    'company_id' => $beitId,
                    'type' => 'course',
                    'name' => $course,
                ],
                [
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        $beitId = DB::table('companies')->where('name', 'بيت المصور')->value('id');

        if (! $beitId) {
            return;
        }

        DB::table('company_offerings')
            ->where('company_id', $beitId)
            ->where('type', 'course')
            ->whereIn('name', ['تصوير بالجوال', 'فاشن'])
            ->delete();
    }
};
