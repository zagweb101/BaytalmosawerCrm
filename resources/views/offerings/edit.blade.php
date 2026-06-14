@extends('layouts.app')

@section('title', 'تعديل دورة أو خدمة - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>تعديل دورة أو خدمة</h1>
            <p class="subtitle">{{ $offering->name }}</p>
        </div>
        <a class="button secondary" href="{{ route('offerings.index') }}">رجوع</a>
    </section>

    <form class="form-panel" method="POST" action="{{ route('offerings.update', $offering) }}">
        @method('PUT')
        @include('offerings._form')

        <div class="form-actions">
            <button class="button" type="submit">حفظ التغييرات</button>
            <a class="button secondary" href="{{ route('offerings.index') }}">إلغاء</a>
        </div>
    </form>
@endsection
