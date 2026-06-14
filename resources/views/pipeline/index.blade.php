@extends('layouts.app')

@section('title', 'مسار العملاء - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>مسار العملاء</h1>
            <p class="subtitle">تابع انتقال العملاء من مرحلة الاهتمام الأولى حتى الاشتراك أو إغلاق الفرصة.</p>
        </div>
        @if (auth()->user()?->canDo('customers.create'))
            <a class="button" href="{{ route('customers.create') }}">إضافة عميل</a>
        @endif
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="panel mb-3">
        <form class="toolbar toolbar-wide" method="GET" action="{{ route('pipeline.index') }}">
            <select name="company_id">
                <option value="">كل الشركات</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected($selectedCompanyId === $company->id)>{{ $company->name }}</option>
                @endforeach
            </select>

            <select name="status">
                <option value="">كل الحالات</option>
                @foreach ($statuses as $status => $label)
                    <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="interest">
                <option value="">كل الدورات والخدمات</option>
                @foreach ($interests as $interest)
                    <option value="{{ $interest }}" @selected($selectedInterest === $interest)>{{ $interest }}</option>
                @endforeach
            </select>

            <select name="service_city">
                <option value="">كل المدن</option>
                @foreach ($serviceCities as $city => $label)
                    <option value="{{ $city }}" @selected($selectedCity === $city)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="team_member_id">
                <option value="">كل المسؤولين</option>
                @foreach ($teamMembers as $teamMember)
                    <option value="{{ $teamMember->id }}" @selected($selectedTeamMemberId === $teamMember->id)>{{ $teamMember->name }}</option>
                @endforeach
            </select>

            <button class="button" type="submit">تطبيق</button>
            <a class="button secondary" href="{{ route('pipeline.index') }}">إلغاء</a>
        </form>
        <div class="pager">
            <span>عدد العملاء المطابقين: <strong>{{ $filteredCustomersCount }}</strong></span>
        </div>
    </section>

    <section class="pipeline-board">
        @foreach ($statuses as $status => $label)
            @php
                $customers = $customersByStatus->get($status, collect());
                $nextStatus = $nextStatuses[$status] ?? null;
            @endphp

            <article class="pipeline-column">
                <h2>{{ $label }} <span class="badge">{{ $customers->count() }}</span></h2>

                @forelse ($customers as $customer)
                    <div class="pipeline-card">
                        <strong>{{ $customer->name }}</strong>
                        <span class="muted">{{ $customer->owningCompany?->name }}{{ $customer->interest ? ' - ' . $customer->interest : '' }}</span>
                        <span class="muted">{{ $customer->phone ?: $customer->email ?: 'بدون بيانات تواصل' }}</span>
                        <span class="muted">
                            {{ $customer->service_city ?: 'بدون مدينة' }}
                            @if ($customer->teamMember)
                                - {{ $customer->teamMember->name }}
                            @endif
                        </span>

                        <div class="actions">
                            <a class="button secondary" href="{{ route('customers.show', $customer) }}">تفاصيل</a>

                            @if ($nextStatus && auth()->user()?->canDo('customers.update'))
                                <form method="POST" action="{{ route('pipeline.status.update', $customer) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $nextStatus }}">
                                    <button class="button" type="submit">{{ $statuses[$nextStatus] }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty">لا يوجد عملاء في هذه المرحلة.</div>
                @endforelse
            </article>
        @endforeach
    </section>
@endsection
