@extends('layouts.app')

@section('title', 'العملاء المكررون - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>العملاء المكررون</h1>
            <p class="subtitle">راجع الأرقام أو البريد الإلكتروني التي ظهرت أكثر من مرة قبل دمج أو تنظيف البيانات.</p>
        </div>
        @if (auth()->user()?->canDo('customers.import'))
            <a class="button secondary" href="{{ route('customers.import.create') }}">استيراد العملاء</a>
        @endif
    </section>

    <section class="dashboard-grid">
        <article class="metric-card">
            <span>أرقام جوال مكررة</span>
            <strong>{{ $phoneDuplicates->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>بريد إلكتروني مكرر</span>
            <strong>{{ $emailDuplicates->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>إجمالي تنبيهات التكرار</span>
            <strong>{{ $phoneDuplicates->count() + $emailDuplicates->count() }}</strong>
        </article>
        <article class="metric-card">
            <span>الإجراء المقترح</span>
            <strong>مراجعة</strong>
        </article>
    </section>

    @include('reports._duplicates_table', [
        'title' => 'تكرار حسب رقم الجوال',
        'type' => 'phone',
        'rows' => $phoneDuplicates,
        'emptyText' => 'لا توجد أرقام جوال مكررة.',
    ])

    @include('reports._duplicates_table', [
        'title' => 'تكرار حسب البريد الإلكتروني',
        'type' => 'email',
        'rows' => $emailDuplicates,
        'emptyText' => 'لا يوجد بريد إلكتروني مكرر.',
    ])
@endsection
