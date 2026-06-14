@extends('layouts.app')

@section('title', 'تعديل عضو فريق - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>تعديل عضو فريق</h1>
            <p class="subtitle">{{ $teamMember->name }}</p>
        </div>
        <a class="button secondary" href="{{ route('team-members.index') }}">رجوع</a>
    </section>

    <form class="form-panel" method="POST" action="{{ route('team-members.update', $teamMember) }}">
        @method('PUT')
        @include('team-members._form')

        <div class="form-actions">
            <button class="button" type="submit">حفظ التغييرات</button>
            <a class="button secondary" href="{{ route('team-members.index') }}">إلغاء</a>
        </div>
    </form>
@endsection
