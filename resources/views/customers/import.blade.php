@extends('layouts.app')

@section('title', 'استيراد العملاء - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>استيراد العملاء</h1>
            <p class="subtitle">ارفع ملف CSV من Google Sheet لإضافة أو تحديث قائمة العملاء دفعة واحدة.</p>
        </div>
        <div class="actions">
            <a class="button secondary" href="{{ route('customers.import.template') }}">تحميل قالب CSV</a>
            <a class="button secondary" href="{{ route('customers.index') }}">رجوع</a>
        </div>
    </section>

    <form class="form-panel" method="POST" action="{{ route('customers.import.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <div>
                <label for="default_company_id">الشركة الافتراضية للصفوف غير المحددة</label>
                <select id="default_company_id" name="default_company_id" required>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('default_company_id') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label for="customers_file">ملف العملاء CSV</label>
                <input id="customers_file" type="file" name="customers_file" accept=".csv,text/csv,text/plain" required>
                @error('customers_file') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="note-box mt-panel">
            يفضل استخدام قالب CSV الجاهز. النظام يقبل أيضاً أسماء أعمدة قريبة مثل: الاسم الكامل، رقم الهاتف، الإيميل، المصدر، الحملة، مسؤول المتابعة، الحالة، ملاحظات.
        </div>

        <div class="form-actions">
            <button class="button" type="submit">استيراد العملاء</button>
        </div>
    </form>
@endsection
