@extends('layouts.app')

@section('title', 'لوحة التحكم - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>لوحة التحكم</h1>
            <p class="subtitle">نظرة سريعة على العملاء، التحويلات، والمتابعات التي تحتاج انتباه اليوم.</p>
        </div>
        <div class="actions">
            <a class="button" href="{{ route('alerts.index') }}">ابدأ من التنبيهات</a>
            <a class="button secondary" href="{{ route('follow-ups.today') }}">متابعات اليوم</a>
            @if (auth()->user()?->canDo('customers.create'))
                <a class="button secondary" href="{{ route('customers.create') }}">إضافة عميل</a>
            @endif
        </div>
    </section>

    <section class="dashboard-grid">
        <article class="metric-card">
            <span>إجمالي العملاء</span>
            <strong>{{ $totalCustomers }}</strong>
        </article>
        <article class="metric-card">
            <span>فرص مفتوحة</span>
            <strong>{{ $openLeads }}</strong>
        </article>
        <article class="metric-card">
            <span>مشتركين / عملاء حقيقيين</span>
            <strong>{{ $wonCustomers }}</strong>
        </article>
        <article class="metric-card">
            <span>غير مسندين لمسؤول</span>
            <strong>{{ $unassignedCustomers }}</strong>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="metric-card">
            <span>صفقات مفتوحة</span>
            <strong><a href="{{ route('deals.index') }}">{{ $openDeals }}</a></strong>
        </article>
        <article class="metric-card">
            <span>قيمة الصفقات الرابحة</span>
            <strong>{{ number_format((float) $wonDealValue, 0) }}</strong>
        </article>
        <article class="metric-card">
            <span>مهام مستحقة</span>
            <strong><a href="{{ route('tasks.today') }}">{{ $dueTasks }}</a></strong>
        </article>
        <article class="metric-card">
            <span>تنظيف البيانات</span>
            <strong><a href="{{ route('reports.duplicates') }}">المكررين</a></strong>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="metric-card">
            <span>نسبة التحويل</span>
            <strong>{{ $conversionRate }}%</strong>
        </article>
        <article class="metric-card">
            <span>متابعات مستحقة</span>
            <strong>{{ $dueFollowUps }}</strong>
        </article>
        <article class="metric-card">
            <span>تقرير الفريق</span>
            <strong><a href="{{ route('reports.team') }}">الفريق</a></strong>
        </article>
        <article class="metric-card">
            <span>تقرير الحملات</span>
            <strong><a href="{{ route('reports.campaigns') }}">الحملات</a></strong>
        </article>
        <article class="metric-card">
            <span>توصيات تحتاج إجراء</span>
            <strong><a href="{{ route('alerts.index') }}">{{ $recommendedCustomers->count() }}</a></strong>
        </article>
    </section>

    <section class="panel mt-panel">
        <div class="section-head">
            <h2>توصيات المتابعة</h2>
        </div>
        @if ($recommendedCustomers->count())
            <table>
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الشركة</th>
                        <th>التوصية</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recommendedCustomers as $customer)
                        @php
                            $recommendation = $customer->system_recommendations[0];
                        @endphp
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
                                    @if (auth()->user()?->canDo('followups.create'))
                                        <form method="POST" action="{{ route('customers.recommendations.store', $customer) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="{{ $recommendation['quickAction'] }}">
                                            <button class="button" type="submit">{{ $recommendation['quickActionLabel'] }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد توصيات تحتاج إجراء الآن.</div>
        @endif
    </section>

    <section class="panel mt-panel">
        <div class="section-head">
            <h2>مهام مفتوحة</h2>
        </div>
        @if ($upcomingTasks->count())
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
                    @foreach ($upcomingTasks as $task)
                        <tr>
                            <td>{{ $task->title }}</td>
                            <td>
                                <a class="customer-name" href="{{ route('customers.show', $task->customer) }}">{{ $task->customer->name }}</a>
                                <div class="muted">{{ $task->customer->owningCompany?->name ?: '-' }}</div>
                            </td>
                            <td>{{ $task->teamMember?->name ?: '-' }}</td>
                            <td>{{ $task->due_at?->format('Y-m-d H:i') ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد مهام مفتوحة حاليًا.</div>
        @endif
    </section>

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

    <section class="two-column-grid">
        <article class="panel">
            <div class="section-head">
                <h2>توزيع الحالات</h2>
            </div>
            <div class="status-list">
                @foreach ($statusLabels as $status => $label)
                    @php
                        $count = (int) ($statusCounts[$status] ?? 0);
                        $percentage = $totalCustomers > 0 ? ($count / $totalCustomers) * 100 : 0;
                    @endphp
                    <div class="status-row">
                        <span>{{ $label }}</span>
                        <div class="bar"><span style="width: {{ $percentage }}%"></span></div>
                        <strong>{{ $count }}</strong>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="panel">
            <div class="section-head">
                <h2>المتابعات القادمة</h2>
            </div>
            @if ($upcomingFollowUps->count())
                <table>
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الموعد</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($upcomingFollowUps as $followUp)
                            <tr>
                                <td>
                                    <a class="customer-name" href="{{ route('customers.show', $followUp->customer) }}">{{ $followUp->customer->name }}</a>
                                    <div class="muted">{{ $followUp->customer->owningCompany?->name }}</div>
                                </td>
                                <td>{{ $followUp->due_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">لا توجد متابعات مفتوحة حالياً.</div>
            @endif
        </article>
    </section>

    <section class="panel mt-panel">
        <div class="section-head">
            <h2>آخر الأنشطة</h2>
        </div>
        @if ($recentActivities->count())
            <div class="timeline">
                @foreach ($recentActivities as $activity)
                    <div class="timeline-item">
                        <div class="timeline-time">{{ $activity->created_at->format('Y-m-d H:i') }}</div>
                        <div class="timeline-body">
                            <strong>
                                <a class="customer-name" href="{{ route('customers.show', $activity->customer) }}">{{ $activity->customer->name }}</a>
                            </strong>
                            <span class="badge">{{ $activity->title }}</span>
                            @if ($activity->description)
                                <p>{{ $activity->description }}</p>
                            @endif
                            <div class="muted">
                                {{ $activity->customer->owningCompany?->name ?: '-' }}
                                @if ($activity->teamMember)
                                    - {{ $activity->teamMember->name }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty">لا توجد أنشطة مسجلة حتى الآن.</div>
        @endif
    </section>

    <section class="panel mt-panel">
        <div class="section-head">
            <h2>آخر العملاء</h2>
        </div>
        @if ($recentCustomers->count())
            <table>
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الشركة</th>
                        <th>المسؤول</th>
                        <th>الدورة / الخدمة</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentCustomers as $customer)
                        <tr>
                            <td>
                                <div class="customer-name">{{ $customer->name }}</div>
                                <div class="muted">{{ $customer->phone ?: $customer->email }}</div>
                            </td>
                            <td>{{ $customer->owningCompany?->name }}</td>
                            <td>{{ $customer->teamMember?->name ?: '-' }}</td>
                            <td>{{ $customer->interest ?: '-' }}</td>
                            <td><a class="button secondary" href="{{ route('customers.show', $customer) }}">تفاصيل</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد بيانات عملاء حتى الآن.</div>
        @endif
    </section>

    <div
        class="daily-summary-modal"
        data-summary-date="{{ $dailySummary['date'] }}"
        data-user-id="{{ $dailySummary['user_id'] }}"
        data-due-follow-ups="{{ $dailySummary['due_follow_ups'] }}"
        data-due-tasks="{{ $dailySummary['due_tasks'] }}"
        data-recommendations="{{ $dailySummary['recommendations'] }}"
        data-unassigned-customers="{{ $dailySummary['unassigned_customers'] }}"
        aria-hidden="true"
    >
        <div class="daily-summary-backdrop" data-close-daily-summary></div>
        <section class="daily-summary-panel" role="dialog" aria-modal="true" aria-labelledby="daily_summary_title">
            <div class="section-head daily-summary-head">
                <div>
                    <h2 id="daily_summary_title">ملخص المتابعة اليوم</h2>
                    <div class="muted">{{ now()->format('Y-m-d') }}</div>
                </div>
                <button class="button secondary" type="button" data-close-daily-summary>إغلاق</button>
            </div>
            <div class="padded-panel">
                <div class="dashboard-grid">
                    <article class="metric-card">
                        <span>متابعات مستحقة</span>
                        <strong>{{ $dailySummary['due_follow_ups'] }}</strong>
                    </article>
                    <article class="metric-card">
                        <span>مهام مستحقة</span>
                        <strong>{{ $dailySummary['due_tasks'] }}</strong>
                    </article>
                    <article class="metric-card">
                        <span>توصيات</span>
                        <strong>{{ $dailySummary['recommendations'] }}</strong>
                    </article>
                    <article class="metric-card">
                        <span>عملاء بلا مسؤول</span>
                        <strong>{{ $dailySummary['unassigned_customers'] }}</strong>
                    </article>
                </div>
                <div class="actions">
                    <a class="button" href="{{ route('alerts.index') }}">عرض التنبيهات</a>
                    <a class="button secondary" href="{{ route('tasks.today') }}">مهام اليوم</a>
                    <a class="button secondary" href="{{ route('follow-ups.today') }}">متابعات اليوم</a>
                    <button class="button secondary" type="button" data-close-daily-summary>تم الاطلاع اليوم</button>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.querySelector('[data-summary-date]');

            if (!modal) {
                return;
            }

            const storageKey = `crm-daily-summary-${modal.dataset.userId}-${modal.dataset.summaryDate}`;
            const totalItems =
                Number(modal.dataset.dueFollowUps) +
                Number(modal.dataset.dueTasks) +
                Number(modal.dataset.recommendations) +
                Number(modal.dataset.unassignedCustomers);

            if (totalItems === 0 || localStorage.getItem(storageKey) === 'seen') {
                return;
            }

            const close = () => {
                localStorage.setItem(storageKey, 'seen');
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('daily-summary-open');
            };

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('daily-summary-open');

            modal.querySelectorAll('[data-close-daily-summary]').forEach((button) => {
                button.addEventListener('click', close);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    close();
                }
            });
        });
    </script>
@endsection
