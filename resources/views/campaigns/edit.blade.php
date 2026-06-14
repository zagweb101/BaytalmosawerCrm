@extends('layouts.app')

@section('title', 'تعديل حملة - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>تعديل حملة</h1>
            <p class="subtitle">{{ $campaign->name }}</p>
        </div>
        <a class="button secondary" href="{{ route('campaigns.index') }}">رجوع</a>
    </section>

    <form class="form-panel" method="POST" action="{{ route('campaigns.update', $campaign) }}">
        @method('PUT')
        @include('campaigns._form')

        <div class="form-actions">
            <button class="button" type="submit">حفظ التغييرات</button>
            <a class="button secondary" href="{{ route('campaigns.index') }}">إلغاء</a>
        </div>
    </form>
@endsection
