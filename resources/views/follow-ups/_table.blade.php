<section class="panel mt-panel">
    <div class="section-head">
        <h2>{{ $title }}</h2>
    </div>

    @if ($followUps->count())
        <table>
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>الشركة</th>
                    <th>الملاحظة</th>
                    <th>الموعد</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($followUps as $followUp)
                    <tr>
                        <td>
                            <a class="customer-name" href="{{ route('customers.show', $followUp->customer) }}">{{ $followUp->customer->name }}</a>
                            <div class="muted">{{ $followUp->customer->phone ?: $followUp->customer->email }}</div>
                        </td>
                        <td>{{ $followUp->customer->owningCompany?->name ?: '-' }}</td>
                        <td>{{ $followUp->note }}</td>
                        <td>{{ $followUp->due_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('follow-ups.complete', $followUp) }}">
                                @csrf
                                @method('PATCH')
                                <button class="button secondary" type="submit">إغلاق</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">{{ $emptyText }}</div>
    @endif
</section>
