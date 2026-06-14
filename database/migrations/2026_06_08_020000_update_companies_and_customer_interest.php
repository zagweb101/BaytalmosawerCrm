<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('activity')->nullable()->after('name');
            $table->text('lead_goal')->nullable()->after('activity');
        });

        DB::table('companies')->where('id', 1)->update([
            'name' => 'بيت المصور',
            'activity' => 'تعليمي في مجال التصوير الفوتوغرافي',
            'lead_goal' => 'متابعة المهتمين القادمين من إعلانات التواصل وجوجل حتى يصبحوا مشتركين حقيقيين في الدورة المطلوبة.',
        ]);

        DB::table('companies')->where('id', 2)->update([
            'name' => 'فيدا برودكشن',
            'activity' => 'ميديا برودكشن وتصوير منتجات وصناعة محتوى مرئي',
            'lead_goal' => 'متابعة العملاء المحتملين الراغبين في تصوير منتجاتهم أو إنتاج محتوى مرئي حتى يصبحوا عملاء حقيقيين.',
        ]);

        Schema::table('customers', function (Blueprint $table) {
            $table->string('interest')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('interest');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['activity', 'lead_goal']);
        });
    }
};
