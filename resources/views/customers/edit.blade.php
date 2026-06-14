@extends('layouts.app')

@section('title', 'تعديل عميل - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>تعديل بيانات العميل</h1>
            <p class="subtitle">{{ $customer->name }}{{ $customer->company ? ' - ' . $customer->company : '' }}</p>
        </div>
        <a class="button secondary" href="{{ route('customers.index') }}">رجوع</a>
    </section>

    <form class="form-panel" method="POST" action="{{ route('customers.update', $customer) }}">
        @method('PUT')
        @include('customers._form')

        <div class="form-actions">
            <button class="button" type="submit">حفظ التغييرات</button>
            <a class="button secondary" href="{{ route('customers.index') }}">إلغاء</a>
        </div>
    </form>
@endsection
