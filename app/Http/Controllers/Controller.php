<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

abstract class Controller
{
    protected function allowedCompanyIds(): ?array
    {
        return auth()->user()?->allowedCompanyIds();
    }

    protected function applyCompanyScope(Builder $query, string $column = 'company_id'): Builder
    {
        $companyIds = $this->allowedCompanyIds();

        if ($companyIds !== null) {
            $query->whereIn($column, $companyIds);
        }

        return $query;
    }

    protected function visibleCompanies(): Collection
    {
        $query = Company::query()->withCount('customers')->orderBy('id');
        $companyIds = $this->allowedCompanyIds();

        if ($companyIds !== null) {
            $query->whereIn('id', $companyIds);
        }

        return $query->get();
    }

    protected function ensureCompanyAccess(?int $companyId): void
    {
        if (! auth()->user()?->canAccessCompany($companyId)) {
            abort(403, 'ليست لديك صلاحية الوصول إلى هذه الشركة.');
        }
    }
}
