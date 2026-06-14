@extends('layouts.app')

@section('title', 'الأدوار والصلاحيات - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>الأدوار والصلاحيات</h1>
            <p class="subtitle">أنشئ أدوارًا مخصصة وحدد ما يستطيع كل دور رؤيته أو تنفيذه.</p>
        </div>
        <a class="button" href="{{ route('roles.create') }}">إضافة دور</a>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="panel">
        @if ($roles->count())
            <table>
                <thead>
                    <tr>
                        <th>الدور</th>
                        <th>المستخدمون</th>
                        <th>الصلاحيات</th>
                        <th>النوع</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>
                                <div class="customer-name">{{ $role->name }}</div>
                                <div class="muted">{{ $role->description }}</div>
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td><span class="badge">{{ $role->is_system ? 'نظامي' : 'مخصص' }}</span></td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('roles.edit', $role) }}">تعديل</a>
                                    @if (! $role->is_system && $role->users_count === 0)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('هل تريد حذف هذا الدور؟')">
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

            {{ $roles->links() }}
        @else
            <div class="empty">لا توجد أدوار بعد.</div>
        @endif
    </section>
@endsection
