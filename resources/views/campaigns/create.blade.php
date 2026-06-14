@extends('layouts.app')

@section('title', 'إضافة حملة - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>إضافة حملة</h1>
            <p class="subtitle">أضف حملة إعلانية ليتم ربط العملاء القادمين منها.</p>
        </div>
        <a class="button secondary" href="{{ route('campaigns.index') }}">رجوع</a>
    </section>

    <form class="form-panel" method="POST" action="{{ route('campaigns.store') }}">
        @include('campaigns._form')

        <div class="form-actions">
            <button class="button" type="submit">حفظ الحملة</button>
            <a class="button secondary" href="{{ route('campaigns.index') }}">إلغاء</a>
        </div>
    </form>
@endsection
