@extends('layouts.app')

@section('title', 'التنبيهات - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>التنبيهات</h1>
            <p class="subtitle">الأشياء التي تحتاج انتباهًا الآن: متابعات، مهام، صفقات قاربت موعدها، وعملاء بلا مسؤول.</p>
        </div>
        <a class="button secondary" href="{{ route('dashboard') }}">لوحة التحكم</a>
    </section>

    <section class="dashboard-grid">
        <article class="metric-card">
            <span>متابعات مستحقة</span>
            <strong>{{ $dueFollowUps->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>مهام مستحقة</span>
            <strong>{{ $dueTasks->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>صفقات تحتاج مراجعة</span>
            <strong>{{ $closingDeals->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>عملاء بلا مسؤول</span>
            <strong>{{ $unassignedCustomers->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>توصيات النظام</span>
            <strong>{{ $recommendedCustomers->count() }}</strong>
        </article>
    </section>

    <section class="panel mt-panel">
        <div class="section-head">
            <h2>توصيات النظام</h2>
        </div>
        @if ($recommendedCustomers->count())
            <table>
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الشركة</th>
                        <th>التوصية</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recommendedCustomers as $customer)
                        @foreach ($customer->system_recommendations as $recommendation)
                            <tr>
                                <td>
                                    <a class="customer-name" href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a>
                                    <div class="muted">{{ $customer->phone ?: $customer->email }}</div>
                                </td>
                                <td>{{ $customer->owningCompany?->name ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $recommendation['priority'] === 'high' ? 'danger-badge' : '' }}">{{ $recommendation['title'] }}</span>
                                    <div class="muted">{{ $recommendation['message'] }}</div>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a class="button secondary" href="{{ $recommendation['actionUrl'] }}">{{ $recommendation['actionLabel'] }}</a>
                                        <form method="POST" action="{{ route('customers.recommendations.store', $customer) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="{{ $recommendation['quickAction'] }}">
                                            <button class="button" type="submit">{{ $recommendation['quickActionLabel'] }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد توصيات تحتاج إجراء الآن.</div>
        @endif
    </section>

    <section class="panel mt-panel">
        <div class="section-head">
            <h2>المتابعات المستحقة</h2>
        </div>
        @if ($dueFollowUps->count())
            <table>
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الملاحظة</th>
                        <th>الموعد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dueFollowUps as $followUp)
                        <tr>
                            <td><a class="customer-name" href="{{ route('customers.show', $followUp->customer) }}">{{ $followUp->customer->name }}</a></td>
                            <td>{{ $followUp->note }}</td>
                            <td>{{ $followUp->due_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد متابعات مستحقة.</div>
        @endif
    </section>

    <section class="panel mt-panel">
        <div class="section-head">
            <h2>المهام المستحقة</h2>
        </div>
        @if ($dueTasks->count())
            <table>
                <thead>
                    <tr>
                        <th>المهمة</th>
                        <th>العميل</th>
                        <th>المسؤول</th>
                        <th>الموعد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dueTasks as $task)
                        <tr>
                            <td>{{ $task->title }}</td>
                            <td><a class="customer-name" href="{{ route('customers.show', $task->customer) }}">{{ $task->customer->name }}</a></td>
                            <td>{{ $task->teamMember?->name ?: '-' }}</td>
                            <td>{{ $task->due_at?->format('Y-m-d H:i') ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد مهام مستحقة.</div>
        @endif
    </section>

    <section class="panel mt-panel">
        <div class="section-head">
            <h2>صفقات تحتاج مراجعة</h2>
        </div>
        @if ($closingDeals->count())
            <table>
                <thead>
                    <tr>
                        <th>الصفقة</th>
                        <th>العميل</th>
                        <th>الشركة</th>
                        <th>القيمة</th>
                        <th>تاريخ الإغلاق</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($closingDeals as $deal)
                        <tr>
                            <td>{{ $deal->title }}</td>
                            <td><a class="customer-name" href="{{ route('customers.show', $deal->customer) }}">{{ $deal->customer->name }}</a></td>
                            <td>{{ $deal->company?->name ?: '-' }}</td>
                            <td>{{ number_format((float) $deal->amount, 2) }}</td>
                            <td>{{ $deal->expected_close_date?->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد صفقات متأخرة عن موعد الإغلاق المتوقع.</div>
        @endif
    </section>

    <section class="panel mt-panel">
        <div class="section-head">
            <h2>عملاء بلا مسؤول</h2>
        </div>
        @if ($unassignedCustomers->count())
            <table>
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الشركة التابعة لنا</th>
                        <th>الجوال / البريد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($unassignedCustomers as $customer)
                        <tr>
                            <td><a class="customer-name" href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></td>
                            <td>{{ $customer->owningCompany?->name ?: '-' }}</td>
                            <td>{{ $customer->phone ?: $customer->email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">كل العملاء لديهم مسؤول متابعة.</div>
        @endif
    </section>
@endsection
