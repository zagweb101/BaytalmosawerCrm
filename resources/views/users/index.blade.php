@extends('layouts.app')

@section('title', 'مستخدمي النظام - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>مستخدمي النظام</h1>
            <p class="subtitle">إدارة حسابات الدخول وتحديد دور كل مستخدم داخل CRM.</p>
        </div>
        <a class="button" href="{{ route('users.create') }}">إضافة مستخدم</a>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="panel">
        @if ($users->count())
            <table>
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد</th>
                        <th>الدور</th>
                        <th>الشركات</th>
                        <th>آخر تحديث</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="customer-name">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge">{{ $user->is_super_admin ? 'سوبر أدمن' : ($user->roleModel?->name ?? ($roles[$user->role] ?? $user->role)) }}</span>
                            </td>
                            <td>
                                @if ($user->is_super_admin || $user->isManager() || $user->companies->isEmpty())
                                    <span class="muted">كل الشركات</span>
                                @else
                                    {{ $user->companies->pluck('name')->join('، ') }}
                                @endif
                            </td>
                            <td>{{ $user->updated_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('users.edit', $user) }}">تعديل</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('هل تريد حذف هذا المستخدم؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button danger" type="submit">حذف</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $users->links() }}
        @else
            <div class="empty">لا يوجد مستخدمون بعد.</div>
        @endif
    </section>
@endsection
