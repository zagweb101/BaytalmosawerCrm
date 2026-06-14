@extends('layouts.app')

@section('title', 'العملاء - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>لوحة العملاء</h1>
            <p class="subtitle">تابع رحلة العميل من إعلان أو منصة حتى يصبح مشتركاً في دورة بيت المصور أو عميلاً حقيقياً في فيدا برودكشن.</p>
        </div>
        <div class="actions">
            @if (auth()->user()?->canDo('customers.import'))
                <a class="button secondary" href="{{ route('customers.import.create') }}">استيراد من شيت</a>
            @endif
            @if (auth()->user()?->canDo('reports.export'))
                <a class="button secondary" href="{{ route('exports.customers') }}">تصدير العملاء</a>
            @endif
            @if (auth()->user()?->canDo('customers.create'))
                <a class="button" href="{{ route('customers.create') }}">إضافة عميل جديد</a>
            @endif
        </div>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="company-grid">
        @foreach ($companies as $company)
            <article class="company-card">
                <div class="company-meta">
                    <span class="badge company-badge">{{ $company->name }}</span>
                    <span class="badge">{{ $company->customers_count }} عميل</span>
                </div>
                <h2>{{ $company->activity }}</h2>
                <p>{{ $company->lead_goal }}</p>
            </article>
        @endforeach
    </section>

    <section class="stats">
        <div class="stat">
            <span>إجمالي العملاء</span>
            <strong>{{ $totalCustomers }}</strong>
        </div>
        <div class="stat">
            <span>عملاء فعليون</span>
            <strong>{{ $activeCustomers }}</strong>
        </div>
        <div class="stat">
            <span>فرص مفتوحة</span>
            <strong>{{ $openLeads }}</strong>
        </div>
    </section>

    <section class="panel">
        <form class="toolbar toolbar-wide" method="GET" action="{{ route('customers.index') }}">
            <input name="q" value="{{ request('q') }}" placeholder="ابحث بالاسم، شركة العميل، الدورة، الخدمة، البريد أو الجوال">
            <select name="company_id">
                <option value="">كل الشركات التابعة لنا</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected((int) request('company_id') === $company->id)>{{ $company->name }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">كل الحالات</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="source">
                <option value="">كل المصادر</option>
                @foreach ($sources as $source)
                    <option value="{{ $source }}" @selected(request('source') === $source)>{{ $source }}</option>
                @endforeach
            </select>
            <select name="interest">
                <option value="">كل الدورات والخدمات</option>
                @foreach ($interests as $interest)
                    <option value="{{ $interest }}" @selected(request('interest') === $interest)>{{ $interest }}</option>
                @endforeach
            </select>
            <select name="campaign_id">
                <option value="">كل الحملات</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->id }}" @selected((int) request('campaign_id') === $campaign->id)>{{ $campaign->name }}</option>
                @endforeach
            </select>
            <select name="team_member_id">
                <option value="">كل المسؤولين</option>
                @foreach ($teamMembers as $teamMember)
                    <option value="{{ $teamMember->id }}" @selected((int) request('team_member_id') === $teamMember->id)>{{ $teamMember->name }}</option>
                @endforeach
            </select>
            <button class="button" type="submit">بحث</button>
        </form>

        @if ($customers->count())
            <table>
                <thead>
                    <tr>
                        <th>الشركة التابعة لنا</th>
                        <th>العميل</th>
                        <th>التواصل</th>
                        <th>الحالة</th>
                        <th>الدورة / الخدمة</th>
                        <th>الحملة</th>
                        <th>المسؤول</th>
                        <th>القيمة</th>
                        <th>المتابعة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>
                                <span class="badge company-badge">{{ $customer->owningCompany?->name ?: 'غير محدد' }}</span>
                            </td>
                            <td>
                                <div class="customer-name">{{ $customer->name }}</div>
                                <div class="muted">{{ $customer->company ?: 'بدون شركة عميل' }}</div>
                            </td>
                            <td>
                                <div>{{ $customer->phone ?: '-' }}</div>
                                <div class="muted">{{ $customer->email ?: '-' }}</div>
                                @if(filled($customer->address))
                                    <div class="muted">{{ $customer->address }}</div>
                                @endif
                                @if(filled($customer->social_url))
                                    <div><a href="{{ $customer->social_url }}" target="_blank" rel="noopener">رابط التواصل</a></div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $customer->status }}">{{ $statuses[$customer->status] ?? $customer->status }}</span>
                                @if ($customer->source)
                                    <div class="muted">{{ $customer->source }}</div>
                                @endif
                            </td>
                            <td>{{ $customer->interest ?: '-' }}</td>
                            <td>{{ $customer->campaign?->name ?: '-' }}</td>
                            <td>{{ $customer->teamMember?->name ?: '-' }}</td>
                            <td>{{ $customer->value ? number_format((float) $customer->value, 2) : '-' }}</td>
                            <td>{{ $customer->next_follow_up?->format('Y-m-d') ?: '-' }}</td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('customers.show', $customer) }}">تفاصيل</a>
                                    @if (auth()->user()?->canDo('customers.update'))
                                        <a class="button secondary" href="{{ route('customers.edit', $customer) }}">تعديل</a>
                                    @endif
                                    @if (auth()->user()?->canDo('customers.delete'))
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('هل تريد حذف هذا العميل؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button danger" type="submit">حذف</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($customers->hasPages())
                <nav class="pager">
                    <span>صفحة {{ $customers->currentPage() }} من {{ $customers->lastPage() }}</span>
                    <div class="pager-actions">
                        @if ($customers->onFirstPage())
                            <span class="button secondary disabled">السابق</span>
                        @else
                            <a class="button secondary" href="{{ $customers->previousPageUrl() }}">السابق</a>
                        @endif

                        @if ($customers->hasMorePages())
                            <a class="button secondary" href="{{ $customers->nextPageUrl() }}">التالي</a>
                        @else
                            <span class="button secondary disabled">التالي</span>
                        @endif
                    </div>
                </nav>
            @endif
        @else
            <div class="empty">لا توجد بيانات عملاء حتى الآن.</div>
        @endif
    </section>
@endsection
