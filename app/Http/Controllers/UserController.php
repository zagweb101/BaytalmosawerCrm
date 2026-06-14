<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::with(['roleModel', 'companies'])->latest()->paginate(12),
            'roles' => $this->roles(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(['role' => 'sales']),
            'roles' => Role::orderBy('name')->get(),
            'companies' => Company::orderBy('id')->get(),
            'selectedCompanies' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data = $this->applySuperAdminFlag($request, $data);
        $companyIds = $this->extractCompanyIds($request, $data);
        $user = User::create($this->onlyUserFields($this->withLegacyRole($data)));
        $this->syncUserCompanies($user, $companyIds);

        return redirect()->route('users.index')->with('success', 'تمت إضافة المستخدم.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user->load(['companies', 'roleModel']),
            'roles' => Role::orderBy('name')->get(),
            'companies' => Company::orderBy('id')->get(),
            'selectedCompanies' => $user->companies->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validatedData($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data = $this->applySuperAdminFlag($request, $data);
        $companyIds = $this->extractCompanyIds($request, $data);
        $user->update($this->onlyUserFields($this->withLegacyRole($data)));
        $this->syncUserCompanies($user, $companyIds);

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('success', 'لا يمكن حذف حسابك الحالي.');
        }

        if ($user->isManager() && User::where('role', 'manager')->count() <= 1) {
            return back()->with('success', 'يجب أن يبقى مدير واحد على الأقل.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم.');
    }

    private function validatedData(Request $request, ?User $user = null): array
    {
        if (! $request->filled('role_id') && $request->filled('role')) {
            $legacyRoleId = Role::where('slug', $request->string('role')->toString())->value('id');

            if ($legacyRoleId) {
                $request->merge(['role_id' => $legacyRoleId]);
            }
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'is_super_admin' => ['nullable', 'boolean'],
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', 'exists:companies,id'],
        ]);
    }

    private function roles(): array
    {
        return [
            'manager' => 'مدير',
            'sales' => 'مبيعات',
        ];
    }

    private function withLegacyRole(array $data): array
    {
        $role = Role::find($data['role_id']);

        if ($role?->slug === 'super_admin' || ! empty($data['is_super_admin'])) {
            $data['role'] = 'manager';
            $data['is_super_admin'] = true;
        } elseif ($role?->slug === 'manager') {
            $data['role'] = 'manager';
            $data['is_super_admin'] = false;
        } else {
            $data['role'] = 'sales';
            $data['is_super_admin'] = false;
        }

        return $data;
    }

    private function onlyUserFields(array $data): array
    {
        $allowed = [
            'name', 'email', 'password', 'role', 'role_id', 'email_verified_at', 'is_super_admin',
        ];

        return array_intersect_key($data, array_flip($allowed));
    }

    private function extractCompanyIds(Request $request, array $data): ?array
    {
        if ($request->boolean('is_super_admin')) {
            return [];
        }

        $ids = $data['company_ids'] ?? $request->input('company_ids', []);

        return is_array($ids) ? array_values(array_filter(array_map('intval', $ids))) : [];
    }

    private function applySuperAdminFlag(Request $request, array $data): array
    {
        if ($request->boolean('is_super_admin')) {
            $roleId = Role::where('slug', 'super_admin')->value('id');

            if ($roleId) {
                $data['role_id'] = $roleId;
            }

            $data['is_super_admin'] = true;
            $data['role'] = 'manager';
        } else {
            $data['is_super_admin'] = false;
        }

        return $data;
    }

    private function syncUserCompanies(User $user, ?array $companyIds): void
    {
        if ($companyIds === null) {
            return;
        }

        $user->companies()->sync($companyIds);
    }
}
