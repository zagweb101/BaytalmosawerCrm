@extends('layouts.app')

@section('title', 'إضافة دور - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>إضافة دور</h1>
            <p class="subtitle">حدد اسم الدور والصلاحيات التي سيحصل عليها المستخدمون.</p>
        </div>
        <a class="button secondary" href="{{ route('roles.index') }}">رجوع</a>
    </section>

    <section class="form-panel">
        @include('roles._form', [
            'action' => route('roles.store'),
            'method' => 'POST',
        ])
    </section>
@endsection
