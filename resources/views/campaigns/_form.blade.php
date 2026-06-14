@csrf

<div class="form-grid">
    <div>
        <label for="company_id">الشركة</label>
        <select id="company_id" name="company_id" required>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected((int) old('company_id', $campaign->company_id) === $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
        @error('company_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="name">اسم الحملة</label>
        <input id="name" name="name" value="{{ old('name', $campaign->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="channel">القناة</label>
        <select id="channel" name="channel">
            <option value="">غير محدد</option>
            @foreach ($channels as $value => $label)
                <option value="{{ $value }}" @selected(old('channel', $campaign->channel) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('channel') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="budget">الميزانية</label>
        <input id="budget" type="number" min="0" step="0.01" name="budget" value="{{ old('budget', $campaign->budget) }}">
        @error('budget') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="started_at">تاريخ البداية</label>
        <input id="started_at" type="date" name="started_at" value="{{ old('started_at', optional($campaign->started_at)->format('Y-m-d')) }}">
        @error('started_at') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="ended_at">تاريخ النهاية</label>
        <input id="ended_at" type="date" name="ended_at" value="{{ old('ended_at', optional($campaign->ended_at)->format('Y-m-d')) }}">
        @error('ended_at') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <label class="check-row" for="is_active">
            <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $campaign->is_active))>
            <span>الحملة نشطة</span>
        </label>
        @error('is_active') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>
