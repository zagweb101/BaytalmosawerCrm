<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Deal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealController extends Controller
{
    public function index(Request $request): View
    {
        $stage = $request->string('stage')->trim()->toString();
        $companyId = $request->integer('company_id') ?: null;

        $query = Deal::query()
            ->with(['customer', 'company', 'teamMember'])
            ->when($stage, fn ($query) => $query->where('stage', $stage))
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->latest();
        $this->applyCompanyScope($query);

        $stageCounts = $this->applyCompanyScope(Deal::query())
            ->selectRaw('stage, COUNT(*) as total')
            ->groupBy('stage')
            ->pluck('total', 'stage');

        return view('deals.index', [
            'deals' => $query->paginate(12)->withQueryString(),
            'companies' => $this->visibleCompanies(),
            'selectedStage' => $stage,
            'selectedCompanyId' => $companyId,
            'stages' => $this->stages(),
            'stageCounts' => $stageCounts,
        ]);
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);

        $data = $request->validate([
            'team_member_id' => ['nullable', 'exists:team_members,id'],
            'title' => ['required', 'string', 'max:255'],
            'stage' => ['required', 'in:new,proposal,negotiation,won,lost'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['amount'] = $data['amount'] ?? 0;

        $deal = $customer->deals()->create($data + [
            'company_id' => $customer->company_id,
            'team_member_id' => $data['team_member_id'] ?? $customer->team_member_id,
        ]);

        $customer->recordActivity(
            'deal_added',
            'تمت إضافة صفقة',
            $deal->title,
            $deal->team_member_id,
            [
                'deal_id' => $deal->id,
                'stage' => $deal->stage,
                'amount' => $deal->amount,
            ],
        );

        return redirect()->route('customers.show', $customer)->with('success', 'تمت إضافة الصفقة.');
    }

    public function updateStage(Request $request, Deal $deal): RedirectResponse
    {
        $this->ensureCompanyAccess($deal->company_id);

        $data = $request->validate([
            'stage' => ['required', 'in:new,proposal,negotiation,won,lost'],
        ]);
        $oldStage = $deal->stage;

        $deal->update($data);

        if ($oldStage !== $deal->stage) {
            $stages = $this->stages();
            $deal->customer?->recordActivity(
                'deal_stage_changed',
                'تم تغيير مرحلة الصفقة',
                $deal->title . ': من ' . ($stages[$oldStage] ?? $oldStage) . ' إلى ' . ($stages[$deal->stage] ?? $deal->stage),
                $deal->team_member_id,
                [
                    'deal_id' => $deal->id,
                    'from' => $oldStage,
                    'to' => $deal->stage,
                ],
            );
        }

        return back()->with('success', 'تم تحديث مرحلة الصفقة.');
    }

    private function stages(): array
    {
        return [
            'new' => 'فرصة جديدة',
            'proposal' => 'عرض سعر / تفاصيل',
            'negotiation' => 'تفاوض',
            'won' => 'تم الفوز',
            'lost' => 'مغلقة / خاسرة',
        ];
    }
}
