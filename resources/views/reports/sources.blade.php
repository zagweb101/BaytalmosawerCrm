@extends('layouts.app')

@section('title', 'مصادر العملاء - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>مصادر العملاء</h1>
            <p class="subtitle">اعرف من أين يأتي العملاء، وما المصادر التي تتحول إلى مشتركين أو عملاء حقيقيين.</p>
        </div>
        <a class="button secondary" href="{{ route('dashboard') }}">لوحة التحكم</a>
    </section>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('reports.sources') }}">
            <select name="company_id">
                <option value="">كل الشركات</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected($selectedCompanyId === $company->id)>{{ $company->name }}</option>
                @endforeach
            </select>
            <button class="button" type="submit">تطبيق</button>
        </form>

        @if ($rows->count())
            <table>
                <thead>
                    <tr>
                        <th>المصدر</th>
                        <th>إجمالي العملاء</th>
                        <th>فرص مفتوحة</th>
                        <th>مشتركين / عملاء حقيقيين</th>
                        <th>نسبة التحويل</th>
                        <th>عرض العملاء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php
                            $conversion = $row->total > 0 ? round(($row->won / $row->total) * 100, 1) : 0;
                            $sourceValue = $row->source_name === 'غير محدد' ? '' : $row->source_name;
                        @endphp
                        <tr>
                            <td class="customer-name">{{ $row->source_name }}</td>
                            <td>{{ $row->total }}</td>
                            <td>{{ $row->open_count }}</td>
                            <td>{{ $row->won }}</td>
                            <td>{{ $conversion }}%</td>
                            <td>
                                @if ($sourceValue)
                                    <a class="button secondary" href="{{ route('customers.index', ['source' => $sourceValue, 'company_id' => $selectedCompanyId]) }}">العملاء</a>
                                @else
                                    <span class="muted">غير متاح</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد مصادر مسجلة حتى الآن.</div>
        @endif
    </section>
@endsection
