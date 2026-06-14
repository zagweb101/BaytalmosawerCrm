<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FollowUp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FollowUpController extends Controller
{
    public function today(): View
    {
        $baseQuery = FollowUp::query()
            ->with(['customer.owningCompany'])
            ->whereHas('customer', fn ($query) => $this->applyCompanyScope($query))
            ->whereNull('completed_at')
            ->whereNotNull('due_at');

        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        return view('follow-ups.today', [
            'overdueFollowUps' => (clone $baseQuery)
                ->where('due_at', '<', $todayStart)
                ->oldest('due_at')
                ->get(),
            'todayFollowUps' => (clone $baseQuery)
                ->whereBetween('due_at', [$todayStart, $todayEnd])
                ->oldest('due_at')
                ->get(),
            'upcomingFollowUps' => (clone $baseQuery)
                ->where('due_at', '>', $todayEnd)
                ->oldest('due_at')
                ->limit(30)
                ->get(),
        ]);
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);

        $data = $request->validate([
            'type' => ['required', 'in:call,whatsapp,email,meeting,other'],
            'note' => ['required', 'string'],
            'due_at' => ['nullable', 'date'],
        ]);

        $followUp = $customer->followUps()->create($data);

        if (! empty($data['due_at'])) {
            $customer->update(['next_follow_up' => Carbon::parse($data['due_at'])->toDateString()]);
        }

        $customer->recordActivity(
            'follow_up_added',
            'تمت إضافة متابعة',
            $followUp->note,
            metadata: [
                'follow_up_id' => $followUp->id,
                'type' => $followUp->type,
                'due_at' => $followUp->due_at?->toDateTimeString(),
            ],
        );

        return redirect()->route('customers.show', $customer)->with('success', 'تمت إضافة المتابعة.');
    }

    public function complete(FollowUp $followUp): RedirectResponse
    {
        $followUp->load('customer');
        $this->ensureCompanyAccess($followUp->customer?->company_id);

        $followUp->update(['completed_at' => now()]);
        $followUp->customer?->recordActivity(
            'follow_up_completed',
            'تم إغلاق متابعة',
            $followUp->note,
            metadata: [
                'follow_up_id' => $followUp->id,
                'completed_at' => $followUp->completed_at?->toDateTimeString(),
            ],
        );

        return back()->with('success', 'تم إغلاق المتابعة.');
    }
}
