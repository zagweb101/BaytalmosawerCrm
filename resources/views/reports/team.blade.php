@extends('layouts.app')

@section('title', 'أداء الفريق - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>أداء الفريق</h1>
            <p class="subtitle">تابع توزيع العملاء على مسؤولي المتابعة، والفرص المفتوحة والتحويلات لكل شخص.</p>
        </div>
        <a class="button secondary" href="{{ route('team-members.index') }}">فريق العمل</a>
    </section>

    <section class="dashboard-grid">
        <article class="metric-card">
            <span>أعضاء الفريق</span>
            <strong>{{ $teamMembers->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>عملاء غير مسندين</span>
            <strong>{{ $unassignedCustomers }}</strong>
        </article>
        <article class="metric-card">
            <span>إجمالي العملاء المسندين</span>
            <strong>{{ $teamMembers->sum('customers_count') }}</strong>
        </article>
        <article class="metric-card">
            <span>تحويلات الفريق</span>
            <strong>{{ $teamMembers->sum('won_customers_count') }}</strong>
        </article>
    </section>

    <section class="panel">
        @if ($teamMembers->count())
            <table>
                <thead>
                    <tr>
                        <th>عضو الفريق</th>
                        <th>الدور</th>
                        <th>إجمالي العملاء</th>
                        <th>فرص مفتوحة</th>
                        <th>عملاء حقيقيين</th>
                        <th>مغلق / غير نشط</th>
                        <th>نسبة التحويل</th>
                        <th>عرض العملاء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teamMembers as $teamMember)
                        @php
                            $conversion = $teamMember->customers_count > 0 ? round(($teamMember->won_customers_count / $teamMember->customers_count) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="customer-name">{{ $teamMember->name }}</td>
                            <td>{{ $teamMember->role ?: '-' }}</td>
                            <td>{{ $teamMember->customers_count }}</td>
                            <td>{{ $teamMember->open_customers_count }}</td>
                            <td>{{ $teamMember->won_customers_count }}</td>
                            <td>{{ $teamMember->inactive_customers_count }}</td>
                            <td>{{ $conversion }}%</td>
                            <td><a class="button secondary" href="{{ route('customers.index', ['team_member_id' => $teamMember->id]) }}">العملاء</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا يوجد أعضاء فريق حتى الآن.</div>
        @endif
    </section>
@endsection
