@extends('layouts.app')

@section('title', 'إضافة مستخدم - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>إضافة مستخدم</h1>
            <p class="subtitle">أنشئ حساب دخول جديد للفريق.</p>
        </div>
        <a class="button secondary" href="{{ route('users.index') }}">رجوع</a>
    </section>

    <section class="form-panel">
        @include('users._form', [
            'action' => route('users.store'),
            'method' => 'POST',
        ])
    </section>
@endsection
