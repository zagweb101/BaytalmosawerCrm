<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function index(): View
    {
        return view('team-members.index', [
            'teamMembers' => TeamMember::withCount('customers')->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('team-members.create', [
            'teamMember' => new TeamMember(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TeamMember::create($this->validatedData($request));

        return redirect()->route('team-members.index')->with('success', 'تمت إضافة عضو الفريق.');
    }

    public function edit(TeamMember $teamMember): View
    {
        return view('team-members.edit', [
            'teamMember' => $teamMember,
        ]);
    }

    public function update(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $teamMember->update($this->validatedData($request));

        return redirect()->route('team-members.index')->with('success', 'تم تحديث عضو الفريق.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $teamMember->delete();

        return redirect()->route('team-members.index')->with('success', 'تم حذف عضو الفريق.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }
}
