<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('type')->default('course');
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $beitId = DB::table('companies')->where('name', 'بيت المصور')->value('id');
        $now = now();

        if ($beitId) {
            DB::table('company_offerings')->insert([
                ['company_id' => $beitId, 'type' => 'course', 'name' => 'اساسيات التصوير', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['company_id' => $beitId, 'type' => 'course', 'name' => 'احتراف الاضاءة', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['company_id' => $beitId, 'type' => 'course', 'name' => 'احتراف تصوير الاعراس', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['company_id' => $beitId, 'type' => 'course', 'name' => 'احتراف تصوير الاطعمة', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_offerings');
    }
};
