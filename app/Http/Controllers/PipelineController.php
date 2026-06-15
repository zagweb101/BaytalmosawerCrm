<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\TeamMember;
use App\Support\CustomerStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PipelineController extends Controller
{
    private const BEIT_COMPANY = 'بيت المصور';

    private const VIDA_COMPANY = 'فيدا برودكشن';

    public function index(Request $request): View
    {
        return $this->renderPipeline($request, self::BEIT_COMPANY, 'pipeline.index');
    }

    public function vida(Request $request): View
    {
        return $this->renderPipeline($request, self::VIDA_COMPANY, 'pipeline.vida');
    }

    private function renderPipeline(Request $request, string $companyName, string $routeName): View
    {
        $company = Company::query()->where('name', $companyName)->first();
        $companyId = $company?->id;

        if ($companyId !== null) {
            $this->ensureCompanyAccess($companyId);
        }

        $selectedStatus = $request->string('status')->trim()->toString();
        $selectedInterest = $request->string('interest')->trim()->toString();
        $selectedCity = $request->string('service_city')->trim()->toString();
        $teamMemberId = $request->integer('team_member_id') ?: null;
        $statuses = CustomerStatus::orderedLabelsFor($companyName);
        $statusKeys = array_keys($statuses);

        $customerQuery = Customer::query()
            ->with(['owningCompany', 'teamMember'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId), fn ($query) => $query->whereRaw('1 = 0'))
            ->when($selectedStatus, fn ($query) => $query->where('status', $selectedStatus))
            ->when($selectedInterest, fn ($query) => $query->where('interest', $selectedInterest))
            ->when($selectedCity, fn ($query) => $query->where('service_city', $selectedCity))
            ->when($teamMemberId, fn ($query) => $query->where('team_member_id', $teamMemberId))
            ->whereIn('status', $statusKeys);
        $this->applyCompanyScope($customerQuery);

        $customers = $customerQuery->latest()->get();
        $customersByStatus = $customers->groupBy('status');

        $interestQuery = Customer::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId), fn ($query) => $query->whereRaw('1 = 0'));
        $this->applyCompanyScope($interestQuery);

        [$title, $subtitle] = match ($companyName) {
            self::BEIT_COMPANY => [
                'مسار بيت المصور',
                'تابع انتقال المهتمين بالدورات من الاهتمام الأولى حتى الاشتراك أو إغلاق الفرصة.',
            ],
            self::VIDA_COMPANY => [
                'مسار فيدا برودكشن',
                'تابع فرص الميديا برودكشن من التواصل الأول حتى إغلاق الصفقة أو إيقاف المتابعة.',
            ],
            default => [
                'مسار العملاء',
                'تابع انتقال العملاء بين مراحل المتابعة.',
            ],
        };

        return view('pipeline.index', [
            'pipelineTitle' => $title,
            'pipelineSubtitle' => $subtitle,
            'pipelineRoute' => $routeName,
            'lockedCompany' => $company,
            'teamMembers' => TeamMember::where('is_active', true)->orderBy('name')->get(),
            'interests' => $interestQuery
                ->whereNotNull('interest')
                ->where('interest', '<>', '')
                ->distinct()
                ->orderBy('interest')
                ->pluck('interest'),
            'serviceCities' => $this->serviceCities(),
            'selectedStatus' => $selectedStatus,
            'selectedInterest' => $selectedInterest,
            'selectedCity' => $selectedCity,
            'selectedTeamMemberId' => $teamMemberId,
            'statuses' => $statuses,
            'customersByStatus' => $customersByStatus,
            'filteredCustomersCount' => $customers->count(),
            'nextStatuses' => CustomerStatus::nextStatusesFor($companyName),
            'isVidaPipeline' => $companyName === self::VIDA_COMPANY,
        ]);
    }

    public function updateStatus(Request $request, Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);
        $customer->loadMissing('owningCompany');

        $data = $request->validate([
            'status' => ['required', CustomerStatus::validationRule()],
        ]);

        $oldStatus = $customer->status;
        $companyName = $customer->owningCompany?->name;
        $statuses = CustomerStatus::labelsFor($companyName);

        $customer->update($data);

        if ($oldStatus !== $customer->status) {
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
