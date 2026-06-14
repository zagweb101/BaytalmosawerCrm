@csrf

<div class="form-grid">
    <div>
        <label for="company_id">الشركة</label>
        <select id="company_id" name="company_id" required>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected((int) old('company_id', $offering->company_id) === $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
        @error('company_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="type">النوع</label>
        <select id="type" name="type" required>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $offering->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <label for="name">اسم الدورة أو الخدمة</label>
        <input id="name" name="name" value="{{ old('name', $offering->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <label class="check-row" for="is_active">
            <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $offering->is_active))>
            <span>نشط ويظهر في الاختيارات</span>
        </label>
        @error('is_active') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>
