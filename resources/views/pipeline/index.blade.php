@extends('layouts.app')

@section('title', ($pipelineTitle ?? 'مسار العملاء') . ' - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>{{ $pipelineTitle ?? 'مسار العملاء' }}</h1>
            <p class="subtitle">{{ $pipelineSubtitle ?? 'تابع انتقال العملاء من مرحلة الاهتمام الأولى حتى الاشتراك أو إغلاق الفرصة.' }}</p>
            @if ($lockedCompany)
                <p class="muted">الشركة: <strong>{{ $lockedCompany->name }}</strong></p>
            @endif
        </div>
        @if (auth()->user()?->canDo('customers.create'))
            <a class="button" href="{{ route('customers.create', $lockedCompany ? ['company_id' => $lockedCompany->id] : []) }}">إضافة عميل</a>
        @endif
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="panel mb-3">
        <form class="toolbar toolbar-wide" method="GET" action="{{ route($pipelineRoute ?? 'pipeline.index') }}">
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
            <a class="button secondary" href="{{ route($pipelineRoute ?? 'pipeline.index') }}">إلغاء</a>
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
                        <span class="muted">{{ $customer->interest ?: 'بدون دورة أو خدمة' }}</span>
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

                            @if (($isVidaPipeline ?? false) && ! in_array($customer->status, ['inactive', 'customer'], true) && auth()->user()?->canDo('customers.update'))
                                <form method="POST" action="{{ route('pipeline.status.update', $customer) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="inactive">
                                    <button class="button secondary" type="submit">مفقود</button>
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
