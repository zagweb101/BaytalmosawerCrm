<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <div>
            <label for="name">الاسم</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="email">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="role">الدور</label>
            <select id="role" name="role_id" required>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected((int) old('role_id', $user->role_id) === $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role_id') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="password">كلمة المرور</label>
            <input id="password" type="password" name="password" @required(! $user->exists)>
            @error('password') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="full">
            <label class="check-row">
                <input type="checkbox" name="is_super_admin" value="1" @checked(old('is_super_admin', $user->is_super_admin))>
                <span>سوبر أدمن: يرى كل الشركات وكل أجزاء النظام</span>
            </label>
            @error('is_super_admin') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="full">
            <label>الشركات المسموحة</label>
            <div class="two-column-grid">
                @foreach ($companies as $company)
                    <label class="check-row note-box">
                        <input type="checkbox" name="company_ids[]" value="{{ $company->id }}" @checked(in_array($company->id, old('company_ids', $selectedCompanies), true))>
                        <span>{{ $company->name }}</span>
                    </label>
                @endforeach
            </div>
            <div class="muted mt-2">اتركها فارغة لو لا تريد تقييد المستخدم بشركة محددة.</div>
            @error('company_ids') <div class="error">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-actions">
        <button class="button" type="submit">حفظ</button>
    </div>
</form>
