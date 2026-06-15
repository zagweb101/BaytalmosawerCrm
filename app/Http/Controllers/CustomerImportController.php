<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerImportController extends Controller
{
    public function create(): View
    {
        return view('customers.import', [
            'companies' => $this->visibleCompanies(),
        ]);
    }

    public function template(): StreamedResponse
    {
        $headers = [
            'اسم العميل',
            'رقم الجوال',
            'البريد الإلكتروني',
            'الشركة التابعة لنا',
            'شركة العميل',
            'المصدر',
            'الحملة',
            'القناة',
            'مسؤول المتابعة',
            'الدورة المطلوبة',
            'الخدمة المطلوبة',
            'الحالة',
            'ملاحظات',
        ];

        $rows = [
            [
                'مثال بيت المصور',
                '0500000000',
                'student@example.com',
                'بيت المصور',
                '',
                'إعلان جوجل',
                'حملة اساسيات التصوير - جوجل',
                'Google Ads',
                'أحمد',
                'اساسيات التصوير',
                '',
                'عميل محتمل',
                'مهتم بدورة نهاية الأسبوع',
            ],
            [
                'مثال فيدا',
                '0550000000',
                'client@example.com',
                'فيدا برودكشن',
                'شركة العميل',
                'إنستغرام',
                'حملة تصوير منتجات - إنستغرام',
                'Instagram',
                'سارة',
                '',
                'تصوير منتجات',
                'تم التواصل',
                'يريد عرض سعر',
            ],
        ];

        return response()->streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, 'crm-customers-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'default_company_id' => ['required', 'exists:companies,id'],
            'customers_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);
        $this->ensureCompanyAccess((int) $data['default_company_id']);

        $handle = fopen($request->file('customers_file')->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        $created = 0;
        $updated = 0;
        $skipped = 0;

        if (! $headers) {
            return back()->with('success', 'الملف فارغ أو غير قابل للقراءة.');
        }

        $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $headers);
        $companies = $this->visibleCompanies();

        while (($row = fgetcsv($handle)) !== false) {
            $rowData = $this->combineRow($headers, $row);
            $name = $this->value($rowData, ['name', 'customer_name', 'fullname', 'full_name', 'الاسم', 'الاسم الكامل', 'اسم العميل', 'العميل']);

            if (! $name) {
                $skipped++;
                continue;
            }

            $email = $this->value($rowData, ['email', 'mail', 'البريد', 'الايميل', 'الإيميل', 'البريد الإلكتروني']);
            $phone = $this->value($rowData, ['phone', 'mobile', 'whatsapp', 'telephone', 'الجوال', 'الموبايل', 'الهاتف', 'رقم الهاتف', 'رقم الجوال']);
            $companyId = $this->companyIdFromRow($rowData, $companies, (int) $data['default_company_id']);
            $campaignId = $this->campaignIdFromRow($rowData, $companyId);
            $teamMemberId = $this->teamMemberIdFromRow($rowData);

            $customer = null;

            if ($email || $phone) {
                $customer = $this->applyCompanyScope(Customer::query())
                    ->where(function ($customers) use ($email, $phone) {
                        if ($email) {
                            $customers->orWhere('email', $email);
                        }

                        if ($phone) {
                            $customers->orWhere('phone', $phone);
                        }
                    })
                    ->first();
            }

            $payload = [
                'company_id' => $companyId,
                'campaign_id' => $campaignId,
                'team_member_id' => $teamMemberId,
                'name' => $name,
                'company' => $this->value($rowData, ['company', 'client_company', 'شركة العميل', 'الشركة']),
                'email' => $email,
                'phone' => $phone,
                'status' => $this->statusFromRow($rowData),
                'source' => $this->value($rowData, ['source', 'lead_source', 'المصدر', 'مصدر العميل']),
                'interest' => $this->value($rowData, ['interest', 'course', 'service', 'الدورة', 'الخدمة', 'الدورة المطلوبة', 'الخدمة المطلوبة']),
                'service_city' => $this->serviceCityFromRow($rowData),
                'address' => $this->value($rowData, ['address', 'location', 'العنوان', 'عنوان الخدمة']),
                'notes' => $this->value($rowData, ['notes', 'note', 'ملاحظات', 'الملاحظات']),
            ];

            if ($customer) {
                $customer->update(array_filter($payload, fn ($value) => $value !== null && $value !== ''));
                $updated++;
            } else {
                Customer::create($payload);
                $created++;
            }
        }

        fclose($handle);

        return redirect()
            ->route('customers.index')
            ->with('success', "تم الاستيراد: {$created} جديد، {$updated} تحديث، {$skipped} تم تجاهله.");
    }

    private function combineRow(array $headers, array $row): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            $data[$header] = trim((string) ($row[$index] ?? ''));
        }

        return $data;
    }

    private function value(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            $normalized = $this->normalizeHeader($key);

            if (isset($row[$normalized]) && $row[$normalized] !== '') {
                return $row[$normalized];
            }
        }

        return null;
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;

        return Str::of($header)
            ->lower()
            ->replace([' ', '-', '_', '.', ':', '،'], '')
            ->toString();
    }

    private function companyIdFromRow(array $row, $companies, int $defaultCompanyId): int
    {
        $companyName = $this->value($row, ['crm_company', 'owning_company', 'brand', 'الشركة التابعة لنا', 'الشركة الداخلية', 'الشركة المسؤولة']);

        if (! $companyName) {
            return $defaultCompanyId;
        }

        $company = $companies->first(fn ($item) => str_contains($companyName, $item->name) || str_contains($item->name, $companyName));

        return $company?->id ?? $defaultCompanyId;
    }

    private function campaignIdFromRow(array $row, int $companyId): ?int
    {
        $campaignName = $this->value($row, ['campaign', 'campaign_name', 'الحملة', 'اسم الحملة', 'الحملة الإعلانية']);

        if (! $campaignName) {
            return null;
        }

        return Campaign::firstOrCreate([
            'company_id' => $companyId,
            'name' => $campaignName,
        ], [
            'channel' => $this->value($row, ['channel', 'ad_channel', 'القناة', 'منصة الإعلان']) ?: null,
            'is_active' => true,
        ])->id;
    }

    private function teamMemberIdFromRow(array $row): ?int
    {
        $teamMemberName = $this->value($row, ['owner', 'assigned_to', 'sales', 'مسؤول المتابعة', 'المسؤول', 'الموظف']);

        if (! $teamMemberName) {
            return null;
        }

        return TeamMember::firstOrCreate([
            'name' => $teamMemberName,
        ], [
            'role' => 'متابعة',
            'is_active' => true,
        ])->id;
    }

    private function statusFromRow(array $row): string
    {
        $status = $this->value($row, ['status', 'الحالة']) ?? 'lead';

        return match (true) {
            str_contains($status, 'جديد') => 'lead',
            str_contains($status, 'عرض') => 'contacted',
            str_contains($status, 'اتفاق') => 'customer',
            str_contains($status, 'مفقود') => 'inactive',
            str_contains($status, 'تواصل') => 'contacted',
            str_contains($status, 'متابعة') => 'prospect',
            str_contains($status, 'مشترك'), str_contains($status, 'حقيقي'), str_contains($status, 'customer') => 'customer',
            str_contains($status, 'مغلق'), str_contains($status, 'inactive') => 'inactive',
            default => in_array($status, ['lead', 'contacted', 'prospect', 'customer', 'inactive'], true) ? $status : 'lead',
        };
    }

    private function serviceCityFromRow(array $row): ?string
    {
        $city = $this->value($row, ['service_city', 'city', 'مدينة الخدمة', 'المدينة']);

        if (! $city) {
            return null;
        }

        $cities = [
            'جدة',
            'الرياض',
            'الدمام',
            'مكة',
            'المدينة',
            'الطائف',
            'القاهرة',
            'الاسكندرية',
        ];

        foreach ($cities as $allowedCity) {
            if ($city === $allowedCity) {
                return $allowedCity;
            }
        }

        return null;
    }
}
