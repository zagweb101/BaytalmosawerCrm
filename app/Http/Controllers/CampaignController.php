<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(): View
    {
        return view('campaigns.index', [
            'campaigns' => $this->applyCompanyScope(Campaign::with(['company'])->withCount('customers'))->latest()->paginate(12),
            'channels' => $this->channels(),
        ]);
    }

    public function create(): View
    {
        return view('campaigns.create', [
            'campaign' => new Campaign(['is_active' => true]),
            'companies' => $this->visibleCompanies(),
            'channels' => $this->channels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->ensureCompanyAccess((int) $data['company_id']);

        Campaign::create($data);

        return redirect()->route('campaigns.index')->with('success', 'تمت إضافة الحملة.');
    }

    public function edit(Campaign $campaign): View
    {
        $this->ensureCompanyAccess($campaign->company_id);

        return view('campaigns.edit', [
            'campaign' => $campaign,
            'companies' => $this->visibleCompanies(),
            'channels' => $this->channels(),
        ]);
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->ensureCompanyAccess($campaign->company_id);
        $data = $this->validatedData($request);
        $this->ensureCompanyAccess((int) $data['company_id']);

        $campaign->update($data);

        return redirect()->route('campaigns.index')->with('success', 'تم تحديث الحملة.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $this->ensureCompanyAccess($campaign->company_id);

        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'تم حذف الحملة.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'channel' => ['nullable', 'string', 'max:255'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }

    private function channels(): array
    {
        return [
            'Google Ads' => 'Google Ads',
            'Facebook' => 'Facebook',
            'Instagram' => 'Instagram',
            'TikTok' => 'TikTok',
            'WhatsApp' => 'WhatsApp',
            'Other' => 'أخرى',
        ];
    }
}
