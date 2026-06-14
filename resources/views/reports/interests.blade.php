@extends('layouts.app')

@section('title', 'الدورات والخدمات المطلوبة - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>الدورات والخدمات المطلوبة</h1>
            <p class="subtitle">تابع الطلب على دورات بيت المصور وخدمات فيدا، واعرف أيها يتحول إلى عملاء حقيقيين.</p>
        </div>
        <a class="button secondary" href="{{ route('dashboard') }}">لوحة التحكم</a>
    </section>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('reports.interests') }}">
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
                        <th>الدورة / الخدمة</th>
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
                            $interestValue = $row->interest_name === 'غير محدد' ? '' : $row->interest_name;
                        @endphp
                        <tr>
                            <td class="customer-name">{{ $row->interest_name }}</td>
                            <td>{{ $row->total }}</td>
                            <td>{{ $row->open_count }}</td>
                            <td>{{ $row->won }}</td>
                            <td>{{ $conversion }}%</td>
                            <td>
                                @if ($interestValue)
                                    <a class="button secondary" href="{{ route('customers.index', ['interest' => $interestValue, 'company_id' => $selectedCompanyId]) }}">العملاء</a>
                                @else
                                    <span class="muted">غير متاح</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد دورات أو خدمات مسجلة على العملاء حتى الآن.</div>
        @endif
    </section>
@endsection
