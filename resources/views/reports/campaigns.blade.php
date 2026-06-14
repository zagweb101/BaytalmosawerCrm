@extends('layouts.app')

@section('title', 'تقرير الحملات - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>تقرير الحملات</h1>
            <p class="subtitle">راقب العملاء القادمين من كل حملة ونسبة تحولهم إلى مشتركين أو عملاء حقيقيين.</p>
        </div>
        <a class="button secondary" href="{{ route('campaigns.index') }}">إدارة الحملات</a>
    </section>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('reports.campaigns') }}">
            <select name="company_id">
                <option value="">كل الشركات</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected($selectedCompanyId === $company->id)>{{ $company->name }}</option>
                @endforeach
            </select>
            <button class="button" type="submit">تطبيق</button>
        </form>

        @if ($campaigns->count())
            <table>
                <thead>
                    <tr>
                        <th>الحملة</th>
                        <th>الشركة</th>
                        <th>القناة</th>
                        <th>إجمالي العملاء</th>
                        <th>فرص مفتوحة</th>
                        <th>عملاء حقيقيين</th>
                        <th>نسبة التحويل</th>
                        <th>عرض العملاء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campaigns as $campaign)
                        @php
                            $conversion = $campaign->customers_count > 0 ? round(($campaign->won_customers_count / $campaign->customers_count) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="customer-name">{{ $campaign->name }}</td>
                            <td>{{ $campaign->company?->name }}</td>
                            <td>{{ $campaign->channel ?: '-' }}</td>
                            <td>{{ $campaign->customers_count }}</td>
                            <td>{{ $campaign->open_customers_count }}</td>
                            <td>{{ $campaign->won_customers_count }}</td>
                            <td>{{ $conversion }}%</td>
                            <td><a class="button secondary" href="{{ route('customers.index', ['campaign_id' => $campaign->id]) }}">العملاء</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد حملات مسجلة حتى الآن.</div>
        @endif
    </section>
@endsection
