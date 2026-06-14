<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CustomerCommunicationController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);

        $data = $request->validate([
            'channel' => ['required', 'in:whatsapp,call,instagram,other'],
            'result' => ['required', 'in:contacted,no_answer,requested_details,booked,not_interested,follow_up_tomorrow'],
            'template_key' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string'],
        ]);

        $resultLabel = $this->resultLabels()[$data['result']];
        $channelLabel = $this->channelLabels()[$data['channel']];
        $description = $channelLabel . ' - ' . $resultLabel;

        if (! empty($data['message'])) {
            $description .= "\n\n" . $data['message'];
        }

        $customer->recordActivity(
            'communication_result',
            'تم تسجيل نتيجة التواصل',
            $description,
            metadata: [
                'channel' => $data['channel'],
                'result' => $data['result'],
                'template_key' => $data['template_key'] ?? null,
            ],
        );

        $this->createAutomaticTask($customer, $data['result']);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'تم تسجيل نتيجة التواصل.');
    }

    private function createAutomaticTask(Customer $customer, string $result): void
    {
        $task = match ($result) {
            'no_answer' => [
                'title' => 'متابعة بعد عدم الرد',
                'description' => 'إعادة محاولة التواصل مع العميل بعد عدم الرد.',
                'due_at' => Carbon::tomorrow()->setTime(10, 0),
            ],
            'requested_details' => [
                'title' => 'إرسال تفاصيل الدورة أو الخدمة',
                'description' => 'العميل طلب تفاصيل إضافية. أرسل له التفاصيل المناسبة وسجل النتيجة.',
                'due_at' => now()->addHours(2),
            ],
            'follow_up_tomorrow' => [
                'title' => 'متابعة غدًا',
                'description' => 'تم تحديد متابعة جديدة مع العميل غدًا.',
                'due_at' => Carbon::tomorrow()->setTime(10, 0),
            ],
            default => null,
        };

        if (! $task) {
            return;
        }

        $createdTask = $customer->tasks()->create([
            'team_member_id' => $customer->team_member_id,
            'title' => $task['title'],
            'description' => $task['description'],
            'priority' => $result === 'requested_details' ? 'high' : 'normal',
            'due_at' => $task['due_at'],
        ]);

        $customer->update(['next_follow_up' => $task['due_at']->toDateString()]);

        $customer->recordActivity(
            'task_added',
            'تم إنشاء مهمة تلقائيًا',
            $createdTask->title,
            $createdTask->team_member_id,
            [
                'task_id' => $createdTask->id,
                'source' => 'communication_center',
                'due_at' => $createdTask->due_at?->toDateTimeString(),
            ],
        );
    }

    private function resultLabels(): array
    {
        return [
            'contacted' => 'تم التواصل',
            'no_answer' => 'لم يرد',
            'requested_details' => 'طلب تفاصيل',
            'booked' => 'حجز',
            'not_interested' => 'غير مهتم',
            'follow_up_tomorrow' => 'متابعة غدًا',
        ];
    }

    private function channelLabels(): array
    {
        return [
            'whatsapp' => 'واتساب',
            'call' => 'اتصال',
            'instagram' => 'إنستغرام',
            'other' => 'أخرى',
        ];
    }
}
