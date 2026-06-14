<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerNoteController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);

        $data = $request->validate([
            'team_member_id' => ['nullable', 'exists:team_members,id'],
            'body' => ['required', 'string'],
        ]);

        $note = $customer->customerNotes()->create($data);
        $customer->recordActivity(
            'note_added',
            'تمت إضافة ملاحظة',
            $note->body,
            $note->team_member_id,
            [
                'note_id' => $note->id,
            ],
        );

        return redirect()->route('customers.show', $customer)->with('success', 'تمت إضافة الملاحظة.');
    }

    public function destroy(CustomerNote $customerNote): RedirectResponse
    {
        $customer = $customerNote->customer;
        $this->ensureCompanyAccess($customer?->company_id);

        $body = $customerNote->body;

        $customerNote->delete();
        $customer?->recordActivity('note_deleted', 'تم حذف ملاحظة', $body);

        return back()->with('success', 'تم حذف الملاحظة.');
    }
}
