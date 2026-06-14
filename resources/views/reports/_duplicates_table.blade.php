<section class="panel mt-panel">
    <div class="section-head">
        <h2>{{ $title }}</h2>
    </div>

    @if ($rows->count())
        <table>
            <thead>
                <tr>
                    <th>القيمة</th>
                    <th>عدد السجلات</th>
                    <th>عرض العملاء</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td class="customer-name">{{ $row->duplicate_value }}</td>
                        <td>{{ $row->total }}</td>
                        <td>
                            <a class="button secondary" href="{{ route('customers.index', [$type => $row->duplicate_value, 'q' => $row->duplicate_value]) }}">عرض العملاء</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">{{ $emptyText }}</div>
    @endif
</section>
