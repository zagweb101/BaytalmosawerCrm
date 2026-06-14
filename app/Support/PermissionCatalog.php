<?php

namespace App\Support;

class PermissionCatalog
{
    public static function groups(): array
    {
        return [
            'dashboard' => [
                'label' => 'لوحة التحكم',
                'permissions' => [
                    'dashboard.view' => 'عرض لوحة التحكم',
                    'alerts.view' => 'عرض التنبيهات',
                ],
            ],
            'customers' => [
                'label' => 'العملاء',
                'permissions' => [
                    'customers.view' => 'عرض العملاء',
                    'customers.create' => 'إضافة العملاء',
                    'customers.update' => 'تعديل العملاء',
                    'customers.archive' => 'أرشفة العملاء',
                    'customers.delete' => 'حذف العملاء',
                    'customers.import' => 'استيراد العملاء',
                    'customers.export' => 'تصدير العملاء',
                    'customers.print' => 'طباعة العملاء',
                ],
            ],
            'followups' => [
                'label' => 'المتابعات والمهام',
                'permissions' => [
                    'followups.view' => 'عرض المتابعات',
                    'followups.create' => 'إضافة المتابعات',
                    'followups.update' => 'تعديل المتابعات',
                    'followups.complete' => 'إكمال المتابعات',
                    'tasks.view' => 'عرض المهام',
                    'tasks.create' => 'إضافة المهام',
                    'tasks.complete' => 'إكمال المهام',
                ],
            ],
            'deals' => [
                'label' => 'الصفقات',
                'permissions' => [
                    'deals.view' => 'عرض الصفقات',
                    'deals.create' => 'إضافة الصفقات',
                    'deals.update' => 'تعديل الصفقات',
                    'deals.change_stage' => 'تغيير مرحلة الصفقة',
                    'deals.delete' => 'حذف الصفقات',
                ],
            ],
            'reports' => [
                'label' => 'التقارير',
                'permissions' => [
                    'reports.view' => 'عرض التقارير',
                    'reports.export' => 'تصدير التقارير',
                ],
            ],
            'settings' => [
                'label' => 'الإدارة والإعدادات',
                'permissions' => [
                    'campaigns.manage' => 'إدارة الحملات',
                    'offerings.manage' => 'إدارة الدورات والخدمات',
                    'team.manage' => 'إدارة الفريق',
                    'users.manage' => 'إدارة المستخدمين',
                    'roles.manage' => 'إدارة الأدوار والصلاحيات',
                ],
            ],
        ];
    }

    public static function all(): array
    {
        $permissions = [];

        foreach (self::groups() as $groupKey => $group) {
            foreach ($group['permissions'] as $key => $label) {
                $permissions[$key] = [
                    'group' => $groupKey,
                    'label' => $label,
                ];
            }
        }

        return $permissions;
    }

    public static function salesDefaults(): array
    {
        return [
            'dashboard.view',
            'alerts.view',
            'customers.view',
            'customers.create',
            'customers.update',
            'followups.view',
            'followups.create',
            'followups.update',
            'followups.complete',
            'tasks.view',
            'tasks.create',
            'tasks.complete',
            'deals.view',
            'deals.create',
            'deals.update',
            'deals.change_stage',
            'reports.view',
        ];
    }

    public static function managerDefaults(): array
    {
        return array_keys(self::all());
    }
}
