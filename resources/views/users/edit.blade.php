@extends('layouts.app')

@section('title', 'تعديل مستخدم - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>تعديل مستخدم</h1>
            <p class="subtitle">عدّل بيانات الحساب أو الدور.</p>
        </div>
        <a class="button secondary" href="{{ route('users.index') }}">رجوع</a>
    </section>

    <section class="form-panel">
        @include('users._form', [
            'action' => route('users.update', $user),
            'method' => 'PUT',
        ])
    </section>
@endsection
