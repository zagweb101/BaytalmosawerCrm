@extends('layouts.app')

@section('title', 'الدورات والخدمات - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>الدورات والخدمات</h1>
            <p class="subtitle">أدر دورات بيت المصور وخدمات فيدا برودكشن من هنا بدل تعديلها داخل الكود.</p>
        </div>
        <a class="button" href="{{ route('offerings.create') }}">إضافة عنصر</a>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="panel">
        @if ($offerings->count())
            <table>
                <thead>
                    <tr>
                        <th>الشركة</th>
                        <th>النوع</th>
                        <th>الاسم</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($offerings as $offering)
                        <tr>
                            <td>{{ $offering->company?->name }}</td>
                            <td>{{ $types[$offering->type] ?? $offering->type }}</td>
                            <td class="customer-name">{{ $offering->name }}</td>
                            <td>
                                @if ($offering->is_active)
                                    <span class="badge">نشط</span>
                                @else
                                    <span class="badge inactive">غير نشط</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('offerings.edit', $offering) }}">تعديل</a>
                                    <form method="POST" action="{{ route('offerings.destroy', $offering) }}" onsubmit="return confirm('هل تريد حذف هذا العنصر؟')">
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

            @if ($offerings->hasPages())
                <nav class="pager">
                    <span>صفحة {{ $offerings->currentPage() }} من {{ $offerings->lastPage() }}</span>
                    <div class="pager-actions">
                        @if ($offerings->onFirstPage())
                            <span class="button secondary disabled">السابق</span>
                        @else
                            <a class="button secondary" href="{{ $offerings->previousPageUrl() }}">السابق</a>
                        @endif

                        @if ($offerings->hasMorePages())
                            <a class="button secondary" href="{{ $offerings->nextPageUrl() }}">التالي</a>
                        @else
                            <span class="button secondary disabled">التالي</span>
                        @endif
                    </div>
                </nav>
            @endif
        @else
            <div class="empty">لا توجد دورات أو خدمات حتى الآن.</div>
        @endif
    </section>
@endsection
