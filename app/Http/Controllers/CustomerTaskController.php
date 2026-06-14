<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerTask;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CustomerTaskController extends Controller
{
    public function today(Request $request): View
    {
        $teamMemberId = $request->integer('team_member_id') ?: null;
        $baseQuery = CustomerTask::query()
            ->with(['customer.owningCompany', 'teamMember'])
            ->whereHas('customer', fn ($query) => $this->applyCompanyScope($query))
            ->whereNull('completed_at')
            ->when($teamMemberId, fn ($query) => $query->where('team_member_id', $teamMemberId));

        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        return view('tasks.today', [
            'teamMembers' => TeamMember::where('is_active', true)->orderBy('name')->get(),
            'selectedTeamMemberId' => $teamMemberId,
            'overdueTasks' => (clone $baseQuery)
                ->whereNotNull('due_at')
                ->where('due_at', '<', $todayStart)
                ->oldest('due_at')
                ->get(),
            'todayTasks' => (clone $baseQuery)
                ->whereBetween('due_at', [$todayStart, $todayEnd])
                ->oldest('due_at')
                ->get(),
            'upcomingTasks' => (clone $baseQuery)
                ->where(function ($query) use ($todayEnd) {
                    $query->whereNull('due_at')->orWhere('due_at', '>', $todayEnd);
                })
                ->oldest('due_at')
                ->limit(40)
                ->get(),
            'priorities' => $this->priorities(),
        ]);
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);

        $data = $request->validate([
            'team_member_id' => ['nullable', 'exists:team_members,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,normal,high'],
            'due_at' => ['nullable', 'date'],
        ]);

        $task = $customer->tasks()->create($data);
        $customer->recordActivity(
            'task_added',
            'تمت إضافة مهمة',
            $task->title,
            $task->team_member_id,
            [
                'task_id' => $task->id,
                'due_at' => $task->due_at?->toDateTimeString(),
            ],
        );

        return redirect()->route('customers.show', $customer)->with('success', 'تمت إضافة المهمة.');
    }

    public function complete(CustomerTask $customerTask): RedirectResponse
    {
        $customerTask->load('customer');
        $this->ensureCompanyAccess($customerTask->customer?->company_id);

        $customerTask->update(['completed_at' => now()]);
        $customerTask->customer?->recordActivity(
            'task_completed',
            'تم إغلاق مهمة',
            $customerTask->title,
            $customerTask->team_member_id,
            [
                'task_id' => $customerTask->id,
            ],
        );

        return back()->with('success', 'تم إغلاق المهمة.');
    }

    private function priorities(): array
    {
        return [
            'low' => 'منخفضة',
            'normal' => 'عادية',
            'high' => 'عاجلة',
        ];
    }
}
