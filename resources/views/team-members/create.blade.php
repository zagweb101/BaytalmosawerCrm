@extends('layouts.app')

@section('title', 'إضافة عضو فريق - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>إضافة عضو فريق</h1>
            <p class="subtitle">أضف شخصاً يمكن إسناد العملاء والمتابعات إليه.</p>
        </div>
        <a class="button secondary" href="{{ route('team-members.index') }}">رجوع</a>
    </section>

    <form class="form-panel" method="POST" action="{{ route('team-members.store') }}">
        @include('team-members._form')

        <div class="form-actions">
            <button class="button" type="submit">حفظ عضو الفريق</button>
            <a class="button secondary" href="{{ route('team-members.index') }}">إلغاء</a>
        </div>
    </form>
@endsection
