<?php

namespace App\Support;

class CustomerStatus
{
    public const BEIT_COMPANY = 'بيت المصور';

    public const VIDA_COMPANY = 'فيدا برودكشن';

    public const ALL = ['lead', 'contacted', 'prospect', 'customer', 'inactive'];

    public static function label(string $status, ?string $companyName = null): string
    {
        return self::labelsFor($companyName)[$status] ?? $status;
    }

    public static function labelsFor(?string $companyName = null): array
    {
        if ($companyName === self::VIDA_COMPANY) {
            return [
                'lead' => 'عميل جديد',
                'prospect' => 'قيد المتابعة',
                'contacted' => 'تم ارسال العرض',
                'customer' => 'تم الاتفاق',
                'inactive' => 'مفقود',
            ];
        }

        return [
            'lead' => 'عميل محتمل',
            'contacted' => 'تم التواصل',
            'prospect' => 'قيد المتابعة',
            'customer' => 'مشترك / عميل حقيقي',
            'inactive' => 'مغلق / غير نشط',
        ];
    }

    public static function pipelineOrderFor(?string $companyName = null): array
    {
        if ($companyName === self::VIDA_COMPANY) {
            return ['lead', 'prospect', 'contacted', 'customer', 'inactive'];
        }

        return ['lead', 'contacted', 'prospect', 'customer', 'inactive'];
    }

    public static function orderedLabelsFor(?string $companyName = null): array
    {
        $labels = self::labelsFor($companyName);
        $ordered = [];

        foreach (self::pipelineOrderFor($companyName) as $status) {
            $ordered[$status] = $labels[$status];
        }

        return $ordered;
    }

    public static function nextStatusesFor(?string $companyName = null): array
    {
        if ($companyName === self::VIDA_COMPANY) {
            return [
                'lead' => 'prospect',
                'prospect' => 'contacted',
                'contacted' => 'customer',
                'inactive' => 'lead',
            ];
        }

        return [
            'lead' => 'contacted',
            'contacted' => 'prospect',
            'prospect' => 'customer',
            'customer' => 'inactive',
            'inactive' => 'lead',
        ];
    }

    public static function openStatuses(?string $companyName = null): array
    {
        if ($companyName === self::VIDA_COMPANY) {
            return ['lead', 'prospect', 'contacted'];
        }

        return ['lead', 'contacted', 'prospect'];
    }

    public static function validationRule(): string
    {
        return 'in:' . implode(',', self::ALL);
    }
}
