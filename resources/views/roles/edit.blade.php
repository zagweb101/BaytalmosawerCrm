@extends('layouts.app')

@section('title', 'تعديل دور - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>تعديل دور</h1>
            <p class="subtitle">راجع صلاحيات الدور وحدد ما يناسب مسؤوليات الفريق.</p>
        </div>
        <a class="button secondary" href="{{ route('roles.index') }}">رجوع</a>
    </section>

    <section class="form-panel">
        @include('roles._form', [
            'action' => route('roles.update', $role),
            'method' => 'PUT',
        ])
    </section>
@endsection
