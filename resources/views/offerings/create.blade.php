@extends('layouts.app')

@section('title', 'إضافة دورة أو خدمة - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>إضافة دورة أو خدمة</h1>
            <p class="subtitle">أضف دورة لبيت المصور أو خدمة لفيدا برودكشن.</p>
        </div>
        <a class="button secondary" href="{{ route('offerings.index') }}">رجوع</a>
    </section>

    <form class="form-panel" method="POST" action="{{ route('offerings.store') }}">
        @include('offerings._form')

        <div class="form-actions">
            <button class="button" type="submit">حفظ</button>
            <a class="button secondary" href="{{ route('offerings.index') }}">إلغاء</a>
        </div>
    </form>
@endsection
