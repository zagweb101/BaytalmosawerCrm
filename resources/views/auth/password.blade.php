@extends('layouts.app')

@section('title', 'تغيير كلمة المرور')

@section('content')
    <section class="hero">
        <div>
            <h1>تغيير كلمة المرور</h1>
            <p class="subtitle">حدّث كلمة مرور حسابك الحالي لحماية بيانات العملاء والمتابعات.</p>
        </div>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="form-panel">
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PATCH')

            <div class="form-grid one-column">
                <div>
                    <label for="current_password">كلمة المرور الحالية</label>
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
                    @error('current_password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="password">كلمة المرور الجديدة</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="password_confirmation">تأكيد كلمة المرور الجديدة</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">حفظ كلمة المرور</button>
            </div>
        </form>
    </section>
@endsection
