<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('roles.index', [
            'roles' => Role::withCount(['users', 'permissions'])->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('roles.create', [
            'role' => new Role(),
            'permissionGroups' => PermissionCatalog::groups(),
            'selectedPermissions' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $role = Role::create($data + [
            'slug' => Str::slug($data['name']) ?: Str::random(8),
        ]);

        $this->syncPermissions($role, $request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', 'تمت إضافة الدور.');
    }

    public function edit(Role $role): View
    {
        return view('roles.edit', [
            'role' => $role,
            'permissionGroups' => PermissionCatalog::groups(),
            'selectedPermissions' => $role->permissions()->pluck('key')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validatedData($request, $role);
        $role->update($data);

        $this->syncPermissions($role, $request->input('permissions', []));

        return redirect()->route('roles.index')->with('success', 'تم تحديث الدور.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system || $role->users()->exists()) {
            return back()->with('success', 'لا يمكن حذف دور نظامي أو دور مرتبط بمستخدمين.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'تم حذف الدور.');
    }

    private function validatedData(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role)],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'key')],
        ]);
    }

    private function syncPermissions(Role $role, array $permissionKeys): void
    {
        $permissionIds = Permission::query()
            ->whereIn('key', $permissionKeys)
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);
    }
}
