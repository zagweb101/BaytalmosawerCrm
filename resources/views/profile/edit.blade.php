@extends('layouts.app')

@section('title', 'البروفايل')

@section('content')
    <section class="hero">
        <div>
            <h1>البروفايل</h1>
            <p class="subtitle">حدّث بياناتك وصورتك وكلمة المرور.</p>
        </div>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="form-panel">
        <h2>البيانات الشخصية</h2>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid one-column">
                <div class="avatar-row">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="avatar" class="avatar-preview">
                    @else
                        <div class="avatar-placeholder">{{ mb_substr($user->name, 0, 1) }}</div>
                    @endif
                    <div>
                        <label for="avatar">صورة البروفايل</label>
                        <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
                        @error('avatar') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label for="name">الاسم</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="email">البريد الإلكتروني</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">حفظ البيانات</button>
            </div>
        </form>
    </section>

    <section class="form-panel">
        <h2>تغيير كلمة المرور</h2>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')

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
                <button class="button" type="submit">تغيير كلمة المرور</button>
            </div>
        </form>
    </section>
@endsection
