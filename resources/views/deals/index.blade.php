@extends('layouts.app')

@section('title', 'الصفقات - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>الصفقات</h1>
            <p class="subtitle">فرص البيع الفعلية المرتبطة بالعملاء، من فرصة جديدة حتى الفوز أو الإغلاق.</p>
        </div>
        <div class="actions">
            @if (auth()->user()?->canDo('reports.export'))
                <a class="button secondary" href="{{ route('exports.deals') }}">تصدير الصفقات</a>
            @endif
            <a class="button secondary" href="{{ route('customers.index') }}">قائمة العملاء</a>
        </div>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="dashboard-grid">
        @foreach ($stages as $stage => $label)
            <article class="metric-card">
                <span>{{ $label }}</span>
                <strong>{{ (int) ($stageCounts[$stage] ?? 0) }}</strong>
            </article>
        @endforeach
    </section>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('deals.index') }}">
            <select name="stage">
                <option value="">كل المراحل</option>
                @foreach ($stages as $stage => $label)
                    <option value="{{ $stage }}" @selected($selectedStage === $stage)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="company_id">
                <option value="">كل الشركات</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected($selectedCompanyId === $company->id)>{{ $company->name }}</option>
                @endforeach
            </select>
            <button class="button" type="submit">تصفية</button>
        </form>

        @if ($deals->count())
            <table>
                <thead>
                    <tr>
                        <th>الصفقة</th>
                        <th>العميل</th>
                        <th>الشركة</th>
                        <th>المسؤول</th>
                        <th>القيمة</th>
                        <th>المرحلة</th>
                        <th>تغيير المرحلة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deals as $deal)
                        <tr>
                            <td>
                                <div class="customer-name">{{ $deal->title }}</div>
                                <div class="muted">{{ $deal->expected_close_date?->format('Y-m-d') ?: '-' }}</div>
                            </td>
                            <td><a class="customer-name" href="{{ route('customers.show', $deal->customer) }}">{{ $deal->customer->name }}</a></td>
                            <td>{{ $deal->company?->name ?: '-' }}</td>
                            <td>{{ $deal->teamMember?->name ?: '-' }}</td>
                            <td>{{ number_format((float) $deal->amount, 2) }}</td>
                            <td><span class="badge {{ $deal->stage === 'won' ? 'lead' : ($deal->stage === 'lost' ? 'inactive' : '') }}">{{ $stages[$deal->stage] ?? $deal->stage }}</span></td>
                            <td>
                                @if (auth()->user()?->canDo('deals.change_stage'))
                                    <form class="actions" method="POST" action="{{ route('deals.stage.update', $deal) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="stage">
                                            @foreach ($stages as $stage => $label)
                                                <option value="{{ $stage }}" @selected($deal->stage === $stage)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button class="button secondary" type="submit">حفظ</button>
                                    </form>
                                @else
                                    <span class="muted">عرض فقط</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $deals->links() }}
        @else
            <div class="empty">لا توجد صفقات مطابقة.</div>
        @endif
    </section>
@endsection
