<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerTask;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Services\RecommendationService;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(RecommendationService $recommendations): View
    {
        $todayEnd = Carbon::today()->endOfDay();

        return view('alerts.index', [
            'recommendedCustomers' => $recommendations->active(30, $this->allowedCompanyIds()),
            'dueFollowUps' => FollowUp::with(['customer.owningCompany'])
                ->whereHas('customer', fn ($query) => $this->applyCompanyScope($query))
                ->whereNull('completed_at')
                ->whereNotNull('due_at')
                ->where('due_at', '<=', $todayEnd)
                ->oldest('due_at')
                ->get(),
            'dueTasks' => CustomerTask::with(['customer.owningCompany', 'teamMember'])
                ->whereHas('customer', fn ($query) => $this->applyCompanyScope($query))
                ->whereNull('completed_at')
                ->where(function ($query) use ($todayEnd) {
                    $query->whereNull('due_at')->orWhere('due_at', '<=', $todayEnd);
                })
                ->oldest('due_at')
                ->get(),
            'closingDeals' => $this->applyCompanyScope(Deal::with(['customer', 'company', 'teamMember']))
                ->whereIn('stage', ['new', 'proposal', 'negotiation'])
                ->whereNotNull('expected_close_date')
                ->where('expected_close_date', '<=', Carbon::today())
                ->oldest('expected_close_date')
                ->get(),
            'unassignedCustomers' => $this->applyCompanyScope(Customer::with('owningCompany'))
                ->whereNull('team_member_id')
                ->latest()
                ->limit(30)
                ->get(),
        ]);
    }
}
