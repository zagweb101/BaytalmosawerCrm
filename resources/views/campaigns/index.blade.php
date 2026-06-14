@extends('layouts.app')

@section('title', 'الحملات الإعلانية - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>الحملات الإعلانية</h1>
            <p class="subtitle">سجل حملات السوشيال وجوجل واربط العملاء بها لمعرفة الأداء الحقيقي.</p>
        </div>
        <a class="button" href="{{ route('campaigns.create') }}">إضافة حملة</a>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <section class="panel">
        @if ($campaigns->count())
            <table>
                <thead>
                    <tr>
                        <th>الحملة</th>
                        <th>الشركة</th>
                        <th>القناة</th>
                        <th>العملاء</th>
                        <th>الميزانية</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campaigns as $campaign)
                        <tr>
                            <td class="customer-name">{{ $campaign->name }}</td>
                            <td>{{ $campaign->company?->name }}</td>
                            <td>{{ $channels[$campaign->channel] ?? $campaign->channel ?? '-' }}</td>
                            <td>{{ $campaign->customers_count }}</td>
                            <td>{{ $campaign->budget ? number_format((float) $campaign->budget, 2) : '-' }}</td>
                            <td>
                                @if ($campaign->is_active)
                                    <span class="badge">نشطة</span>
                                @else
                                    <span class="badge inactive">غير نشطة</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button secondary" href="{{ route('campaigns.edit', $campaign) }}">تعديل</a>
                                    <form method="POST" action="{{ route('campaigns.destroy', $campaign) }}" onsubmit="return confirm('هل تريد حذف هذه الحملة؟')">
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

            @if ($campaigns->hasPages())
                <nav class="pager">
                    <span>صفحة {{ $campaigns->currentPage() }} من {{ $campaigns->lastPage() }}</span>
                    <div class="pager-actions">
                        @if ($campaigns->onFirstPage())
                            <span class="button secondary disabled">السابق</span>
                        @else
                            <a class="button secondary" href="{{ $campaigns->previousPageUrl() }}">السابق</a>
                        @endif

                        @if ($campaigns->hasMorePages())
                            <a class="button secondary" href="{{ $campaigns->nextPageUrl() }}">التالي</a>
                        @else
                            <span class="button secondary disabled">التالي</span>
                        @endif
                    </div>
                </nav>
            @endif
        @else
            <div class="empty">لا توجد حملات حتى الآن.</div>
        @endif
    </section>
@endsection
