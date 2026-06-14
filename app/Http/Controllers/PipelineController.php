<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PipelineController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->integer('company_id') ?: null;
        $selectedStatus = $request->string('status')->trim()->toString();
        $selectedInterest = $request->string('interest')->trim()->toString();
        $selectedCity = $request->string('service_city')->trim()->toString();
        $teamMemberId = $request->integer('team_member_id') ?: null;
        $statuses = $this->statuses();

        $customerQuery = Customer::query()
            ->with(['owningCompany', 'teamMember'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($selectedStatus, fn ($query) => $query->where('status', $selectedStatus))
            ->when($selectedInterest, fn ($query) => $query->where('interest', $selectedInterest))
            ->when($selectedCity, fn ($query) => $query->where('service_city', $selectedCity))
            ->when($teamMemberId, fn ($query) => $query->where('team_member_id', $teamMemberId))
            ->whereIn('status', array_keys($statuses));
        $this->applyCompanyScope($customerQuery);

        $customers = $customerQuery->latest()->get();
        $customersByStatus = $customers->groupBy('status');

        return view('pipeline.index', [
            'companies' => $this->visibleCompanies(),
            'teamMembers' => TeamMember::where('is_active', true)->orderBy('name')->get(),
            'interests' => $this->applyCompanyScope(Customer::query())
                ->whereNotNull('interest')
                ->where('interest', '<>', '')
                ->distinct()
                ->orderBy('interest')
                ->pluck('interest'),
            'serviceCities' => $this->serviceCities(),
            'selectedCompanyId' => $companyId,
            'selectedStatus' => $selectedStatus,
            'selectedInterest' => $selectedInterest,
            'selectedCity' => $selectedCity,
            'selectedTeamMemberId' => $teamMemberId,
            'statuses' => $statuses,
            'customersByStatus' => $customersByStatus,
            'filteredCustomersCount' => $customers->count(),
            'nextStatuses' => $this->nextStatuses(),
        ]);
    }

    public function updateStatus(Request $request, Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);

        $data = $request->validate([
            'status' => ['required', 'in:lead,contacted,prospect,customer,inactive'],
        ]);

        $oldStatus = $customer->status;

        $customer->update($data);

        if ($oldStatus !== $customer->status) {
            $statuses = $this->statuses();
            $customer->recordActivity(
                'status_changed',
                'تم نقل العميل في المسار',
                'من ' . ($statuses[$oldStatus] ?? $oldStatus) . ' إلى ' . ($statuses[$customer->status] ?? $customer->status),
                metadata: [
                    'from' => $oldStatus,
                    'to' => $customer->status,
                ],
            );
        }

        return back()->with('success', 'تم نقل العميل إلى المرحلة الجديدة.');
    }

    private function statuses(): array
    {
        return [
            'lead' => 'عميل محتمل',
            'contacted' => 'تم التواصل',
            'prospect' => 'قيد المتابعة',
            'customer' => 'مشترك / عميل حقيقي',
            'inactive' => 'مغلق / غير نشط',
        ];
    }

    private function nextStatuses(): array
    {
        return [
            'lead' => 'contacted',
            'contacted' => 'prospect',
            'prospect' => 'customer',
            'customer' => 'inactive',
            'inactive' => 'lead',
        ];
    }

    private function serviceCities(): array
    {
        return [
            'جدة' => 'جدة',
            'الرياض' => 'الرياض',
            'الدمام' => 'الدمام',
            'مكة' => 'مكة',
            'المدينة' => 'المدينة',
            'الطائف' => 'الطائف',
            'القاهرة' => 'القاهرة',
            'الاسكندرية' => 'الاسكندرية',
        ];
    }
}
