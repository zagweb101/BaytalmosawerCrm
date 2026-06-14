@extends('layouts.app')

@section('title', 'مهام اليوم - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>مهام اليوم</h1>
            <p class="subtitle">قائمة العمل اليومية للفريق: المهام المتأخرة، المستحقة اليوم، والقادمة.</p>
        </div>
        <a class="button secondary" href="{{ route('customers.index') }}">قائمة العملاء</a>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('tasks.today') }}">
            <select name="team_member_id">
                <option value="">كل المسؤولين</option>
                @foreach ($teamMembers as $teamMember)
                    <option value="{{ $teamMember->id }}" @selected($selectedTeamMemberId === $teamMember->id)>{{ $teamMember->name }}</option>
                @endforeach
            </select>
            <button class="button" type="submit">تصفية</button>
            <a class="button secondary" href="{{ route('tasks.today') }}">إلغاء</a>
        </form>
    </section>

    <section class="dashboard-grid mt-panel">
        <article class="metric-card">
            <span>متأخرة</span>
            <strong>{{ $overdueTasks->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>اليوم</span>
            <strong>{{ $todayTasks->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>قادمة أو بدون موعد</span>
            <strong>{{ $upcomingTasks->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>إجمالي مفتوح</span>
            <strong>{{ $overdueTasks->count() + $todayTasks->count() + $upcomingTasks->count() }}</strong>
        </article>
    </section>

    @foreach ([
        'المهام المتأخرة' => [$overdueTasks, 'لا توجد مهام متأخرة.'],
        'مهام اليوم' => [$todayTasks, 'لا توجد مهام مستحقة اليوم.'],
        'مهام قادمة أو بدون موعد' => [$upcomingTasks, 'لا توجد مهام قادمة.'],
    ] as $title => [$tasks, $emptyText])
        <section class="panel mt-panel">
            <div class="section-head">
                <h2>{{ $title }}</h2>
            </div>

            @if ($tasks->count())
                <table>
                    <thead>
                        <tr>
                            <th>المهمة</th>
                            <th>العميل</th>
                            <th>المسؤول</th>
                            <th>الأولوية</th>
                            <th>الموعد</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr>
                                <td>
                                    <div class="customer-name">{{ $task->title }}</div>
                                    <div class="muted">{{ $task->description ?: '-' }}</div>
                                </td>
                                <td>
                                    <a class="customer-name" href="{{ route('customers.show', $task->customer) }}">{{ $task->customer->name }}</a>
                                    <div class="muted">{{ $task->customer->owningCompany?->name ?: '-' }}</div>
                                </td>
                                <td>{{ $task->teamMember?->name ?: '-' }}</td>
                                <td><span class="badge {{ $task->priority === 'high' ? 'prospect' : '' }}">{{ $priorities[$task->priority] ?? $task->priority }}</span></td>
                                <td>{{ $task->due_at?->format('Y-m-d H:i') ?: '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="button secondary" type="submit">إغلاق</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">{{ $emptyText }}</div>
            @endif
        </section>
    @endforeach
@endsection
