<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RecommendationActionController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);

        $data = $request->validate([
            'action' => ['required', 'in:collect_remaining,mark_registered,mark_service_done,send_requested_details,retry_no_answer,stale_open_lead'],
        ]);

        match ($data['action']) {
            'collect_remaining' => $this->createTask(
                $customer,
                'تحصيل باقي المستحق',
                'العميل دفع عربونًا. تواصل معه لتحصيل باقي المستحق.',
                'high',
                now()->addHours(2)
            ),
            'mark_registered' => $this->markRegistered($customer),
            'mark_service_done' => $this->markServiceDone($customer),
            'send_requested_details' => $this->createTask(
                $customer,
                'إرسال التفاصيل المطلوبة',
                'أرسل تفاصيل الدورة أو الخدمة للعميل وسجل نتيجة التواصل.',
                'high',
                now()->addHours(2)
            ),
            'retry_no_answer' => $this->createTask(
                $customer,
                'إعادة محاولة التواصل',
                'آخر محاولة كانت بدون رد. جرّب التواصل مرة أخرى.',
                'normal',
                Carbon::tomorrow()->setTime(10, 0)
            ),
            'stale_open_lead' => $this->createTask(
                $customer,
                'مراجعة عميل قديم مفتوح',
                'راجع حالة العميل وحدد هل يحتاج متابعة أو تحويل لغير نشط.',
                'normal',
                Carbon::tomorrow()->setTime(10, 0)
            ),
        };

        return back()->with('success', 'تم تنفيذ إجراء التوصية.');
    }

    private function createTask(Customer $customer, string $title, string $description, string $priority, Carbon $dueAt): void
    {
        $task = $customer->tasks()->create([
            'team_member_id' => $customer->team_member_id,
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'due_at' => $dueAt,
        ]);

        $customer->update(['next_follow_up' => $dueAt->toDateString()]);

        $customer->recordActivity(
            'recommendation_action',
            'تم تنفيذ توصية النظام',
            $title,
            $task->team_member_id,
            [
                'task_id' => $task->id,
                'due_at' => $task->due_at?->toDateTimeString(),
            ],
        );
    }

    private function markRegistered(Customer $customer): void
    {
        $customer->update([
            'status' => 'customer',
            'fulfillment_status' => 'registered',
        ]);

        $customer->recordActivity(
            'recommendation_action',
            'تم تنفيذ توصية النظام',
            'تم تحويل العميل إلى تم التسجيل.',
        );
    }

    private function markServiceDone(Customer $customer): void
    {
        $customer->update([
            'status' => 'customer',
            'fulfillment_status' => 'delivered',
        ]);

        $customer->recordActivity(
            'recommendation_action',
            'تم تنفيذ توصية النظام',
            'تم تحويل العميل إلى تم تنفيذ الخدمة.',
        );
    }
}
