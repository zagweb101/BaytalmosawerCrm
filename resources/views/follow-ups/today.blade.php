@extends('layouts.app')

@section('title', 'مركز المتابعات - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>مركز المتابعات</h1>
            <p class="subtitle">المتابعات المتأخرة، ومتابعات اليوم، والمواعيد القادمة في شاشة واحدة.</p>
        </div>
        <a class="button secondary" href="{{ route('customers.index') }}">قائمة العملاء</a>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="dashboard-grid">
        <article class="metric-card">
            <span>متأخرة</span>
            <strong>{{ $overdueFollowUps->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>اليوم</span>
            <strong>{{ $todayFollowUps->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>قادمة</span>
            <strong>{{ $upcomingFollowUps->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>إجمالي مفتوح</span>
            <strong>{{ $overdueFollowUps->count() + $todayFollowUps->count() + $upcomingFollowUps->count() }}</strong>
        </article>
    </section>

    @include('follow-ups._table', [
        'title' => 'المتابعات المتأخرة',
        'emptyText' => 'لا توجد متابعات متأخرة.',
        'followUps' => $overdueFollowUps,
    ])

    @include('follow-ups._table', [
        'title' => 'متابعات اليوم',
        'emptyText' => 'لا توجد متابعات مستحقة اليوم.',
        'followUps' => $todayFollowUps,
    ])

    @include('follow-ups._table', [
        'title' => 'المتابعات القادمة',
        'emptyText' => 'لا توجد متابعات قادمة.',
        'followUps' => $upcomingFollowUps,
    ])
@endsection
