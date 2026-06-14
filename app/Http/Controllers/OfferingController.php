<?php

namespace App\Http\Controllers;

use App\Models\CompanyOffering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferingController extends Controller
{
    public function index(): View
    {
        return view('offerings.index', [
            'offerings' => $this->applyCompanyScope(CompanyOffering::with('company'))->latest()->paginate(12),
            'companies' => $this->visibleCompanies(),
            'types' => $this->types(),
        ]);
    }

    public function create(): View
    {
        return view('offerings.create', [
            'offering' => new CompanyOffering(['is_active' => true, 'type' => 'course']),
            'companies' => $this->visibleCompanies(),
            'types' => $this->types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->ensureCompanyAccess((int) $data['company_id']);

        CompanyOffering::create($data);

        return redirect()->route('offerings.index')->with('success', 'تمت إضافة العنصر.');
    }

    public function edit(CompanyOffering $offering): View
    {
        $this->ensureCompanyAccess($offering->company_id);

        return view('offerings.edit', [
            'offering' => $offering,
            'companies' => $this->visibleCompanies(),
            'types' => $this->types(),
        ]);
    }

    public function update(Request $request, CompanyOffering $offering): RedirectResponse
    {
        $this->ensureCompanyAccess($offering->company_id);
        $data = $this->validatedData($request);
        $this->ensureCompanyAccess((int) $data['company_id']);

        $offering->update($data);

        return redirect()->route('offerings.index')->with('success', 'تم تحديث العنصر.');
    }

    public function destroy(CompanyOffering $offering): RedirectResponse
    {
        $this->ensureCompanyAccess($offering->company_id);

        $offering->delete();

        return redirect()->route('offerings.index')->with('success', 'تم حذف العنصر.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'type' => ['required', 'in:course,service'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }

    private function types(): array
    {
        return [
            'course' => 'دورة',
            'service' => 'خدمة',
        ];
    }
}
