<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deal;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function customers(): StreamedResponse
    {
        $customers = $this->applyCompanyScope(Customer::with(['owningCompany', 'campaign', 'teamMember']))
            ->orderBy('id')
            ->cursor();

        return $this->download('customers.csv', [
            'الرقم',
            'اسم العميل',
            'الشركة التابعة لنا',
            'شركة العميل',
            'الجوال',
            'البريد',
            'الحالة',
            'المصدر',
            'الدورة / الخدمة',
            'القيمة المتوقعة',
            'الحالة المالية',
            'المبلغ المدفوع',
            'باقي المستحق',
            'حالة التسجيل / التنفيذ',
            'الحملة',
            'المسؤول',
            'المتابعة القادمة',
            'ملاحظات',
        ], $customers->map(fn ($customer) => [
            $customer->id,
            $customer->name,
            $customer->owningCompany?->name,
            $customer->company,
            $customer->phone,
            $customer->email,
            $customer->status,
            $customer->source,
            $customer->interest,
            $customer->value,
            $customer->payment_status,
            $customer->paid_amount,
            $customer->remaining_amount,
            $customer->fulfillment_status,
            $customer->campaign?->name,
            $customer->teamMember?->name,
            $customer->next_follow_up?->format('Y-m-d'),
            $customer->notes,
        ]));
    }

    public function deals(): StreamedResponse
    {
        $deals = $this->applyCompanyScope(Deal::with(['customer', 'company', 'teamMember']))
            ->orderBy('id')
            ->cursor();

        return $this->download('deals.csv', [
            'الرقم',
            'عنوان الصفقة',
            'العميل',
            'الشركة التابعة لنا',
            'المسؤول',
            'المرحلة',
            'القيمة',
            'تاريخ الإغلاق المتوقع',
            'ملاحظات',
        ], $deals->map(fn ($deal) => [
            $deal->id,
            $deal->title,
            $deal->customer?->name,
            $deal->company?->name,
            $deal->teamMember?->name,
            $deal->stage,
            $deal->amount,
            $deal->expected_close_date?->format('Y-m-d'),
            $deal->notes,
        ]));
    }

    private function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            echo "\xEF\xBB\xBF";
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
