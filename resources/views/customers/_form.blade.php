@csrf

<div class="form-grid">
    <div>
        <label for="company_id">الشركة التابعة لنا</label>
        <select id="company_id" name="company_id" required>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" data-company-name="{{ $company->name }}" @selected((int) old('company_id', $customer->company_id) === $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
        @error('company_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="name">اسم العميل</label>
        <input id="name" name="name" value="{{ old('name', $customer->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="company">شركة العميل</label>
        <input id="company" name="company" value="{{ old('company', $customer->company) }}">
        @error('company') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="email">البريد الإلكتروني</label>
        <input id="email" type="email" name="email" value="{{ old('email', $customer->email) }}">
        @error('email') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="phone">رقم الجوال</label>
        <input id="phone" name="phone" value="{{ old('phone', $customer->phone) }}">
        @error('phone') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="status">الحالة</label>
        <select id="status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $customer->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="source">مصدر العميل</label>
        <input id="source" name="source" value="{{ old('source', $customer->source) }}" placeholder="فيسبوك، إنستغرام، جوجل، واتساب">
        @error('source') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="campaign_id">الحملة الإعلانية</label>
        <select id="campaign_id" name="campaign_id">
            <option value="">بدون حملة محددة</option>
            @foreach ($campaigns as $campaign)
                <option value="{{ $campaign->id }}" data-company-id="{{ $campaign->company_id }}" @selected((int) old('campaign_id', $customer->campaign_id) === $campaign->id)>
                    {{ $campaign->name }} - {{ $campaign->company?->name }}
                </option>
            @endforeach
        </select>
        @error('campaign_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="team_member_id">مسؤول المتابعة</label>
        <select id="team_member_id" name="team_member_id">
            <option value="">غير مسند</option>
            @foreach ($teamMembers as $teamMember)
                <option value="{{ $teamMember->id }}" @selected((int) old('team_member_id', $customer->team_member_id) === $teamMember->id)>{{ $teamMember->name }}</option>
            @endforeach
        </select>
        @error('team_member_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label id="interest_label" for="interest">الدورة أو الخدمة المطلوبة</label>
        <input id="interest" type="hidden" name="interest" value="{{ old('interest', $customer->interest) }}">

        <select id="course_interest" data-initial-interest="{{ old('interest', $customer->interest) }}">
            <option value="">اختر الدورة المطلوبة</option>
            @foreach ($courseOptions as $course)
                <option value="{{ $course }}">{{ $course }}</option>
            @endforeach
        </select>

        <input id="service_interest" value="{{ old('interest', $customer->interest) }}" placeholder="اكتب الخدمة المطلوبة">
        @error('interest') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="service_city">مدينة الخدمة</label>
        <select id="service_city" name="service_city">
            <option value="">اختر المدينة</option>
            @foreach ($serviceCities as $value => $label)
                <option value="{{ $value }}" @selected(old('service_city', $customer->service_city) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('service_city') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="address">العنوان</label>
        <input id="address" name="address" value="{{ old('address', $customer->address) }}" placeholder="الحي، الشارع، أو تفاصيل العنوان">
        @error('address') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="social_url">رابط مواقع التواصل</label>
        <input id="social_url" name="social_url" value="{{ old('social_url', $customer->social_url ?? '') }}">
        @error('social_url') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="value">القيمة المتوقعة</label>
        <input id="value" type="number" min="0" step="0.01" name="value" value="{{ old('value', $customer->value) }}">
        @error('value') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="payment_status">الحالة المالية</label>
        <select id="payment_status" name="payment_status">
            @foreach ($paymentStatuses as $value => $label)
                <option value="{{ $value }}" @selected(old('payment_status', $customer->payment_status ?: 'unpaid') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('payment_status') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="paid_amount">المبلغ المدفوع</label>
        <input id="paid_amount" type="number" min="0" step="0.01" name="paid_amount" value="{{ old('paid_amount', $customer->paid_amount) }}">
        @error('paid_amount') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="fulfillment_status">حالة التسجيل / التنفيذ</label>
        <select id="fulfillment_status" name="fulfillment_status">
            @foreach ($fulfillmentStatuses as $value => $label)
                <option value="{{ $value }}" @selected(old('fulfillment_status', $customer->fulfillment_status ?: 'pending') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('fulfillment_status') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="next_follow_up">موعد المتابعة القادم</label>
        <input id="next_follow_up" type="date" name="next_follow_up" value="{{ old('next_follow_up', optional($customer->next_follow_up)->format('Y-m-d')) }}">
        @error('next_follow_up') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="full">
        <label for="notes">ملاحظات</label>
        <textarea id="notes" name="notes">{{ old('notes', $customer->notes) }}</textarea>
        @error('notes') <div class="error">{{ $message }}</div> @enderror
    </div>
</div>

<script>
    (() => {
        const companySelect = document.getElementById('company_id');
        const statusSelect = document.getElementById('status');
        const label = document.getElementById('interest_label');
        const beitStatuses = @json($beitStatuses ?? []);
        const vidaStatuses = @json($vidaStatuses ?? []);
        const hiddenInterest = document.getElementById('interest');
        const courseSelect = document.getElementById('course_interest');
        const serviceInput = document.getElementById('service_interest');
        const campaignSelect = document.getElementById('campaign_id');
        const photographyCourses = Array.from(courseSelect.options).map((option) => option.value).filter(Boolean);

        const removeTemporaryCourse = () => {
            const temporaryOption = courseSelect.querySelector('[data-temporary-course="true"]');

            if (temporaryOption) {
                temporaryOption.remove();
            }
        };

        const selectCourseValue = (value) => {
            removeTemporaryCourse();

            if (!value || photographyCourses.includes(value)) {
                courseSelect.value = value || '';
                return;
            }

            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            option.dataset.temporaryCourse = 'true';
            courseSelect.append(option);
            courseSelect.value = value;
        };

        const selectedCompanyName = () => {
            const option = companySelect.options[companySelect.selectedIndex];
            return option ? option.dataset.companyName : '';
        };

        const renderStatuses = () => {
            const companyName = selectedCompanyName();
            const statusMap = companyName === 'فيدا برودكشن' ? vidaStatuses : beitStatuses;
            const currentStatus = statusSelect.value;

            statusSelect.innerHTML = '';

            Object.entries(statusMap).forEach(([value, labelText]) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = labelText;

                if (value === currentStatus) {
                    option.selected = true;
                }

                statusSelect.append(option);
            });

            if (!statusSelect.value) {
                statusSelect.value = 'lead';
            }
        };

        const filterCampaigns = () => {
            const companyId = companySelect.value;

            Array.from(campaignSelect.options).forEach((option) => {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                option.hidden = option.dataset.companyId !== companyId;
            });

            const selectedOption = campaignSelect.options[campaignSelect.selectedIndex];

            if (selectedOption && selectedOption.hidden) {
                campaignSelect.value = '';
            }
        };

        const setInterestMode = () => {
            const companyName = selectedCompanyName();
            const currentInterest = hiddenInterest.value;

            if (companyName === 'بيت المصور') {
                label.textContent = 'الدورة المطلوبة';
                courseSelect.style.display = '';
                serviceInput.style.display = 'none';

                selectCourseValue(currentInterest);
                hiddenInterest.value = courseSelect.value;
                return;
            }

            if (companyName === 'فيدا برودكشن') {
                label.textContent = 'الخدمة المطلوبة';
                courseSelect.style.display = 'none';
                serviceInput.style.display = '';

                if (photographyCourses.includes(currentInterest)) {
                    serviceInput.value = '';
                }

                hiddenInterest.value = serviceInput.value;
                return;
            }

            label.textContent = 'الدورة أو الخدمة المطلوبة';
            courseSelect.style.display = 'none';
            serviceInput.style.display = '';
            hiddenInterest.value = serviceInput.value;
        };

        companySelect.addEventListener('change', () => {
            serviceInput.value = '';
            courseSelect.value = '';
            hiddenInterest.value = '';
            removeTemporaryCourse();
            filterCampaigns();
            renderStatuses();
            setInterestMode();
        });

        courseSelect.addEventListener('change', () => {
            hiddenInterest.value = courseSelect.value;
        });

        serviceInput.addEventListener('input', () => {
            hiddenInterest.value = serviceInput.value;
        });

        filterCampaigns();
        renderStatuses();
        setInterestMode();
    })();
</script>
