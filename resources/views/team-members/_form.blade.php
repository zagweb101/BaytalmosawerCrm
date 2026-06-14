@csrf

<div class="form-grid">
    <div>
        <label for="name">اسم عضو الفريق</label>
        <input id="name" name="name" value="{{ old('name', $teamMember->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="role">الدور</label>
        <input id="role" name="role" value="{{ old('role', $teamMember->role) }}" placeholder="مبيعات، متابعة، مدير حساب">
        @error('role') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="email">البريد الإلكتروني</label>
        <input id="email" type="email" name="email" value="{{ old('email', $teamMember->email) }}">
        @error('email') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="phone">رقم الجوال</label>
        <input id="phone" name="phone" value="{{ old('phone', $teamMember->phone) }}">
        @error('phone') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <label class="check-row" for="is_active">
            <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $teamMember->is_active))>
            <span>نشط ويمكن إسناد العملاء إليه</span>
        </label>
        @error('is_active') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>
