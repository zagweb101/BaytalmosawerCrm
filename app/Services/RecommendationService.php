<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class RecommendationService
{
    public function forCustomer(Customer $customer): array
    {
        $customer->loadMissing(['owningCompany', 'activities', 'tasks']);

        $recommendations = [];
        $companyName = $customer->owningCompany?->name ?? '';
        $remainingAmount = $customer->remaining_amount;
        $latestCommunication = $customer->activities
            ->where('type', 'communication_result')
            ->first();

        if ($customer->payment_status === 'deposit') {
            $amountText = $remainingAmount !== null ? ' المتبقي: ' . number_format($remainingAmount, 2) : '';
            $recommendations[] = $this->item(
                'collect_remaining',
                'high',
                'طالب بباقي المستحق',
                'هذا العميل دفع عربونًا ولم يكتمل الدفع بعد.' . $amountText,
                'فتح العميل',
                route('customers.show', $customer),
                'collect_remaining',
                'إنشاء مهمة تحصيل'
            );
        }

        if ($customer->payment_status === 'paid' && str_contains($companyName, 'بيت المصور') && $customer->fulfillment_status === 'pending') {
            $recommendations[] = $this->item(
                'mark_registered',
                'high',
                'حوّله إلى تم التسجيل',
                'العميل مكتمل الدفع في بيت المصور ولم يتم تسجيله بعد.',
                'تحديث العميل',
                route('customers.edit', $customer),
                'mark_registered',
                'تحويل إلى تم التسجيل'
            );
        }

        if ($customer->payment_status === 'paid' && str_contains($companyName, 'فيدا') && ! in_array($customer->fulfillment_status, ['delivered', 'completed'], true)) {
            $recommendations[] = $this->item(
                'mark_service_done',
                'high',
                'تابع تنفيذ الخدمة',
                'العميل مكتمل الدفع في فيدا. راجع حالة التنفيذ أو حوّله إلى تم تنفيذ الخدمة عند الانتهاء.',
                'تحديث العميل',
                route('customers.edit', $customer),
                'mark_service_done',
                'تحويل إلى تم تنفيذ الخدمة'
            );
        }

        if ($latestCommunication) {
            $result = $latestCommunication->metadata['result'] ?? null;
            $daysSinceCommunication = $latestCommunication->created_at?->diffInDays(now()) ?? 0;

            if ($result === 'requested_details' && $daysSinceCommunication >= 1) {
                $recommendations[] = $this->item(
                    'send_requested_details',
                    'normal',
                    'أرسل التفاصيل المطلوبة',
                    'آخر نتيجة تواصل أن العميل طلب تفاصيل. تأكد من إرسال تفاصيل الدورة أو الخدمة.',
                    'فتح مركز التواصل',
                    route('customers.show', $customer),
                    'send_requested_details',
                    'إنشاء مهمة إرسال التفاصيل'
                );
            }

            if ($result === 'no_answer' && $daysSinceCommunication >= 1) {
                $recommendations[] = $this->item(
                    'retry_no_answer',
                    'normal',
                    'أعد محاولة التواصل',
                    'آخر محاولة كانت بدون رد. يفضل تجربة رسالة متابعة أو اتصال جديد.',
                    'فتح العميل',
                    route('customers.show', $customer),
                    'retry_no_answer',
                    'إنشاء مهمة إعادة التواصل'
                );
            }
        }

        if (in_array($customer->status, ['lead', 'contacted', 'prospect'], true) && $customer->updated_at?->lt(now()->subDays(7))) {
            $recommendations[] = $this->item(
                'stale_open_lead',
                'low',
                'راجع العميل القديم',
                'هذا العميل مفتوح منذ فترة بدون تحديث حديث. راجع هل يحتاج متابعة أو تحويل لغير نشط.',
                'مراجعة العميل',
                route('customers.show', $customer),
                'stale_open_lead',
                'إنشاء مهمة مراجعة'
            );
        }

        return $recommendations;
    }

    public function active(int $limit = 30, ?array $companyIds = null): Collection
    {
        return Customer::query()
            ->with(['owningCompany', 'activities'])
            ->when($companyIds !== null, fn ($query) => $query->whereIn('company_id', $companyIds))
            ->where(function ($query) {
                $query
                    ->where('payment_status', 'deposit')
                    ->orWhere(function ($paidCustomers) {
                        $paidCustomers
                            ->where('payment_status', 'paid')
                            ->where('fulfillment_status', 'pending');
                    })
                    ->orWhereIn('status', ['lead', 'contacted', 'prospect']);
            })
            ->latest()
            ->limit($limit * 3)
            ->get()
            ->map(function (Customer $customer) {
                $customer->setAttribute('system_recommendations', $this->forCustomer($customer));
                return $customer;
            })
            ->filter(fn (Customer $customer) => count($customer->system_recommendations) > 0)
            ->take($limit)
            ->values();
    }

    private function item(
        string $key,
        string $priority,
        string $title,
        string $message,
        string $actionLabel,
        string $actionUrl,
        string $quickAction,
        string $quickActionLabel
    ): array {
        return compact('key', 'priority', 'title', 'message', 'actionLabel', 'actionUrl', 'quickAction', 'quickActionLabel');
    }
}
