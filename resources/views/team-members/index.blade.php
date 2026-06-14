@extends('layouts.app')

@section('title', 'فريق العمل - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>فريق العمل</h1>
            <p class="subtitle">أضف مسؤولي المتابعة والمبيعات، ثم أسند كل عميل إلى شخص واضح.</p>
        </div>
        <a class="button" href="{{ route('team-members.create') }}">إضافة عضو</a>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="panel">
        @if ($teamMembers->count())
            <table>
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الدور</th>
                        <th>التواصل</th>
                        <th>العملاء المسندون</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teamMembers as $teamMember)
                        <tr>
                            <td class="customer-name">{{ $teamMember->name }}</td>
                            <td>{{ $teamMember->role ?: '-' }}</td>
                            <td>
                                <div>{{ $teamMember->phone ?: '-' }}</div>
                                <div class="muted">{{ $teamMember->email ?: '-' }}</div>
                            </td>
                            <td>{{ $teamMember->customers_count }}</td>
                            <td>
                                @if ($teamMember->is_active)
                                    <span class="badge">نشط</span>
                                @else
                                    <span class="badge inactive">غير نشط</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('team-members.edit', $teamMember) }}">تعديل</a>
                                    <form method="POST" action="{{ route('team-members.destroy', $teamMember) }}" onsubmit="return confirm('هل تريد حذف عضو الفريق؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger" type="submit">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($teamMembers->hasPages())
                <nav class="pager">
                    <span>صفحة {{ $teamMembers->currentPage() }} من {{ $teamMembers->lastPage() }}</span>
                    <div class="pager-actions">
                        @if ($teamMembers->onFirstPage())
                            <span class="button secondary disabled">السابق</span>
                        @else
                            <a class="button secondary" href="{{ $teamMembers->previousPageUrl() }}">السابق</a>
                        @endif

                        @if ($teamMembers->hasMorePages())
                            <a class="button secondary" href="{{ $teamMembers->nextPageUrl() }}">التالي</a>
                        @else
                            <span class="button secondary disabled">التالي</span>
                        @endif
                    </div>
                </nav>
            @endif
        @else
            <div class="empty">لا يوجد أعضاء فريق حتى الآن.</div>
        @endif
    </section>
@endsection
