@extends('layouts.app')

@section('title', 'إضافة عميل - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>إضافة عميل</h1>
            <p class="subtitle">حدد الشركة التابعة لنا، ثم سجل مصدر العميل والدورة أو خدمة الإنتاج التي يهتم بها.</p>
        </div>
        <a class="button secondary" href="{{ route('customers.index') }}">رجوع</a>
    </section>

    <form class="form-panel" method="POST" action="{{ route('customers.store') }}">
        @include('customers._form')

        <div class="form-actions">
            <button class="button" type="submit">حفظ العميل</button>
            <a class="button secondary" href="{{ route('customers.index') }}">إلغاء</a>
        </div>
    </form>
@endsection
