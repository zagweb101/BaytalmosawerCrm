<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Campaign;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sources(Request $request): View
    {
        $companyId = $request->integer('company_id') ?: null;

        $baseQuery = Customer::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId));
        $this->applyCompanyScope($baseQuery);

        $rows = (clone $baseQuery)
            ->selectRaw("COALESCE(NULLIF(source, ''), 'غير محدد') as source_name")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'customer' THEN 1 ELSE 0 END) as won")
            ->selectRaw("SUM(CASE WHEN status IN ('lead', 'contacted', 'prospect') THEN 1 ELSE 0 END) as open_count")
            ->groupBy('source_name')
            ->orderByDesc('total')
            ->get();

        $total = (clone $baseQuery)->count();

        return view('reports.sources', [
            'companies' => $this->visibleCompanies(),
            'selectedCompanyId' => $companyId,
            'rows' => $rows,
            'total' => $total,
        ]);
    }

    public function interests(Request $request): View
    {
        $companyId = $request->integer('company_id') ?: null;

        $baseQuery = Customer::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId));
        $this->applyCompanyScope($baseQuery);

        $rows = (clone $baseQuery)
            ->selectRaw("COALESCE(NULLIF(interest, ''), 'غير محدد') as interest_name")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'customer' THEN 1 ELSE 0 END) as won")
            ->selectRaw("SUM(CASE WHEN status IN ('lead', 'contacted', 'prospect') THEN 1 ELSE 0 END) as open_count")
            ->groupBy('interest_name')
            ->orderByDesc('total')
            ->get();

        return view('reports.interests', [
            'companies' => $this->visibleCompanies(),
            'selectedCompanyId' => $companyId,
            'rows' => $rows,
        ]);
    }

    public function duplicates(): View
    {
        $phoneDuplicates = $this->applyCompanyScope(Customer::query())
            ->whereNotNull('phone')
            ->where('phone', '<>', '')
            ->selectRaw('phone as duplicate_value, COUNT(*) as total')
            ->groupBy('phone')
            ->having('total', '>', 1)
            ->orderByDesc('total')
            ->get();

        $emailDuplicates = $this->applyCompanyScope(Customer::query())
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->selectRaw('email as duplicate_value, COUNT(*) as total')
            ->groupBy('email')
            ->having('total', '>', 1)
            ->orderByDesc('total')
            ->get();

        return view('reports.duplicates', [
            'phoneDuplicates' => $phoneDuplicates,
            'emailDuplicates' => $emailDuplicates,
        ]);
    }

    public function campaigns(Request $request): View
    {
        $companyId = $request->integer('company_id') ?: null;

        $campaignQuery = Campaign::query()
            ->with('company')
            ->withCount([
                'customers as customers_count',
                'customers as won_customers_count' => fn ($query) => $query->where('status', 'customer'),
                'customers as open_customers_count' => fn ($query) => $query->whereIn('status', ['lead', 'contacted', 'prospect']),
            ])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->latest();
        $this->applyCompanyScope($campaignQuery);
        $campaigns = $campaignQuery->get();

        return view('reports.campaigns', [
            'companies' => $this->visibleCompanies(),
            'selectedCompanyId' => $companyId,
            'campaigns' => $campaigns,
        ]);
    }

    public function team(): View
    {
        $teamMembers = TeamMember::query()
            ->withCount([
                'customers as customers_count' => fn ($query) => $this->applyCompanyScope($query),
                'customers as open_customers_count' => fn ($query) => $this->applyCompanyScope($query)->whereIn('status', ['lead', 'contacted', 'prospect']),
                'customers as won_customers_count' => fn ($query) => $this->applyCompanyScope($query)->where('status', 'customer'),
                'customers as inactive_customers_count' => fn ($query) => $this->applyCompanyScope($query)->where('status', 'inactive'),
            ])
            ->orderBy('name')
            ->get();

        return view('reports.team', [
            'teamMembers' => $teamMembers,
            'unassignedCustomers' => $this->applyCompanyScope(Customer::query())->whereNull('team_member_id')->count(),
        ]);
    }
}
