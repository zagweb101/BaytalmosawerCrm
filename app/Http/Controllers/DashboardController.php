<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\CustomerTask;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Services\RecommendationService;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(RecommendationService $recommendations): View
    {
        $statusLabels = [
            'lead' => 'عميل محتمل',
            'contacted' => 'تم التواصل',
            'prospect' => 'قيد المتابعة',
            'customer' => 'مشترك / عميل حقيقي',
            'inactive' => 'مغلق / غير نشط',
        ];

        $statusCounts = $this->applyCompanyScope(Customer::query())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalCustomers = $this->applyCompanyScope(Customer::query())->count();
        $wonCustomers = (int) ($statusCounts['customer'] ?? 0);
        $recommendedCustomers = $recommendations->active(8, $this->allowedCompanyIds());
        $dueFollowUpsCount = FollowUp::whereHas('customer', fn ($query) => $this->applyCompanyScope($query))
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<=', Carbon::today()->endOfDay())
            ->count();
        $dueTasksCount = CustomerTask::whereHas('customer', fn ($query) => $this->applyCompanyScope($query))
            ->whereNull('completed_at')
            ->where(function ($query) {
                $query->whereNull('due_at')->orWhere('due_at', '<=', Carbon::today()->endOfDay());
            })
            ->count();

        return view('dashboard.index', [
            'totalCustomers' => $totalCustomers,
            'openLeads' => $this->applyCompanyScope(Customer::query())->whereIn('status', ['lead', 'contacted', 'prospect'])->count(),
            'wonCustomers' => $wonCustomers,
            'unassignedCustomers' => $this->applyCompanyScope(Customer::query())->whereNull('team_member_id')->count(),
            'openDeals' => $this->applyCompanyScope(Deal::query())->whereIn('stage', ['new', 'proposal', 'negotiation'])->count(),
            'wonDealValue' => $this->applyCompanyScope(Deal::query())->where('stage', 'won')->sum('amount'),
            'conversionRate' => $totalCustomers > 0 ? round(($wonCustomers / $totalCustomers) * 100, 1) : 0,
            'dueFollowUps' => $dueFollowUpsCount,
            'dueTasks' => $dueTasksCount,
            'recommendedCustomers' => $recommendedCustomers,
            'dailySummary' => [
                'date' => now()->toDateString(),
                'user_id' => auth()->id(),
                'due_follow_ups' => $dueFollowUpsCount,
                'due_tasks' => $dueTasksCount,
                'recommendations' => $recommendedCustomers->count(),
                'unassigned_customers' => $this->applyCompanyScope(Customer::query())->whereNull('team_member_id')->count(),
            ],
            'companies' => $this->visibleCompanies(),
            'statusLabels' => $statusLabels,
            'statusCounts' => $statusCounts,
            'recentCustomers' => $this->applyCompanyScope(Customer::with(['owningCompany', 'teamMember']))->latest()->limit(6)->get(),
            'upcomingFollowUps' => FollowUp::with(['customer.owningCompany'])
                ->whereHas('customer', fn ($query) => $this->applyCompanyScope($query))
                ->whereNull('completed_at')
                ->whereNotNull('due_at')
                ->oldest('due_at')
                ->limit(6)
                ->get(),
            'recentActivities' => CustomerActivity::with(['customer.owningCompany', 'teamMember'])
                ->whereHas('customer', fn ($query) => $this->applyCompanyScope($query))
                ->latest()
                ->limit(8)
                ->get(),
            'upcomingTasks' => CustomerTask::with(['customer.owningCompany', 'teamMember'])
                ->whereHas('customer', fn ($query) => $this->applyCompanyScope($query))
                ->whereNull('completed_at')
                ->oldest('due_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
