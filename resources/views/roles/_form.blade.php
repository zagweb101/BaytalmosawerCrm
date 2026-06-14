<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <div>
            <label for="name">اسم الدور</label>
            <input id="name" name="name" value="{{ old('name', $role->name) }}" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="description">وصف مختصر</label>
            <input id="description" name="description" value="{{ old('description', $role->description) }}">
            @error('description') <div class="error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mt-panel">
        <div class="section-head">
            <h2>الصلاحيات</h2>
        </div>

        <div class="padded-panel">
            <div class="two-column-grid">
                @foreach ($permissionGroups as $group)
                    <div class="note-box">
                        <strong>{{ $group['label'] }}</strong>
                        <div class="mt-2">
                            @foreach ($group['permissions'] as $key => $label)
                                <label class="check-row">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, old('permissions', $selectedPermissions), true))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permissions') <div class="error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-actions">
        <button class="button" type="submit">حفظ الدور</button>
    </div>
</form>
