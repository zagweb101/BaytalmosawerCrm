@extends('layouts.app')

@section('title', $customer->name . ' - CRM Laravel')

@section('content')
    <section class="hero">
        <div>
            <h1>{{ $customer->name }}</h1>
            <p class="subtitle">
                {{ $customer->owningCompany?->name ?: 'بدون شركة تابعة' }}
                @if ($customer->interest)
                    - {{ $customer->interest }}
                @endif
            </p>
        </div>
        <div class="actions">
            <a class="button secondary" href="{{ route('customers.index') }}">رجوع</a>
            <a class="button" href="{{ route('customers.edit', $customer) }}">تعديل العميل</a>
        </div>
    </section>

    @if (session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    @php
        $defaultTemplateKey = array_key_first($communicationCenter['templates']);
        $defaultTemplate = $communicationCenter['templates'][$defaultTemplateKey];
    @endphp

    <nav class="tabs customer-tabs" aria-label="أقسام العميل">
        <button class="tab-button active" type="button" data-customer-tab-button="overview">نظرة عامة</button>
        <button class="tab-button" type="button" data-customer-tab-button="communication">مركز التواصل</button>
        <button class="tab-button" type="button" data-customer-tab-button="work">المهام والمتابعات</button>
        <button class="tab-button" type="button" data-customer-tab-button="deals">الصفقات والدفع</button>
        <button class="tab-button" type="button" data-customer-tab-button="notes">الملاحظات والنشاط</button>
    </nav>

    <section class="panel mb-3" data-customer-tab="overview">
        <div class="section-head">
            <h2>توصية النظام</h2>
        </div>

        @if (count($recommendations))
            <div class="status-list">
                @foreach ($recommendations as $recommendation)
                    <div class="note-box recommendation-card {{ $recommendation['priority'] === 'high' ? 'high-priority' : '' }}">
                        <div class="actions" style="justify-content: space-between;">
                            <strong>{{ $recommendation['title'] }}</strong>
                            <span class="badge {{ $recommendation['priority'] === 'high' ? 'danger-badge' : '' }}">{{ $recommendation['priority'] === 'high' ? 'أولوية عالية' : 'متابعة' }}</span>
                        </div>
                        <p style="margin: 10px 0 0;">{{ $recommendation['message'] }}</p>
                        <div class="form-actions">
                            <a class="button secondary" href="{{ $recommendation['actionUrl'] }}">{{ $recommendation['actionLabel'] }}</a>
                            <form method="POST" action="{{ route('customers.recommendations.store', $customer) }}">
                                @csrf
                                <input type="hidden" name="action" value="{{ $recommendation['quickAction'] }}">
                                <button class="button" type="submit">{{ $recommendation['quickActionLabel'] }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty">لا توجد توصية عاجلة لهذا العميل الآن.</div>
        @endif
    </section>

    <section class="panel mb-3" data-customer-tab="communication" data-communication-center data-whatsapp-phone="{{ $communicationCenter['customer_whatsapp_phone'] }}">
        <div class="section-head">
            <h2>مركز التواصل</h2>
        </div>

        <div class="padded-panel">
            <div class="form-grid">
                <div>
                    <label for="communication_template">قالب الرسالة</label>
                    <select id="communication_template">
                        @foreach ($communicationCenter['templates'] as $key => $template)
                            <option value="{{ $key }}" data-message="{{ $template['message'] }}" @selected($key === $defaultTemplateKey)>{{ $template['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="communication_channel">طريقة التواصل المستخدمة</label>
                    <select id="communication_channel">
                        @foreach ($communicationCenter['channels'] as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="full">
                    <label for="communication_message">الرسالة الجاهزة</label>
                    <textarea id="communication_message">{{ $defaultTemplate['message'] }}</textarea>
                </div>
            </div>

            <div class="actions mt-3">
                @if ($communicationCenter['customer_whatsapp_phone'])
                    <a id="communication_whatsapp_button" class="button" href="https://wa.me/{{ $communicationCenter['customer_whatsapp_phone'] }}?text={{ urlencode($defaultTemplate['message']) }}" target="_blank" rel="noopener" data-channel="whatsapp">فتح واتساب</a>
                @else
                    <span class="button secondary disabled" aria-disabled="true">واتساب غير متاح</span>
                @endif

                @if ($communicationCenter['customer_tel'])
                    <a class="button secondary" href="tel:{{ $communicationCenter['customer_tel'] }}" data-channel="call">اتصال</a>
                @else
                    <span class="button secondary disabled" aria-disabled="true">اتصال غير متاح</span>
                @endif

                @if ($communicationCenter['instagram_url'])
                    <a class="button secondary" href="{{ $communicationCenter['instagram_url'] }}" target="_blank" rel="noopener" data-channel="instagram">إنستغرام الشركة</a>
                @endif

                <button class="button secondary" type="button" id="copy_communication_message">نسخ الرسالة</button>
            </div>

            <div class="mt-3">
                <label>تسجيل نتيجة التواصل</label>
                <div class="actions">
                    @foreach ($communicationCenter['results'] as $result => $label)
                        <form class="communication-result-form" method="POST" action="{{ route('customers.communication.store', $customer) }}">
                            @csrf
                            <input class="communication-channel-input" type="hidden" name="channel" value="whatsapp">
                            <input class="communication-template-input" type="hidden" name="template_key" value="{{ $defaultTemplateKey }}">
                            <textarea class="communication-message-input" name="message" hidden>{{ $defaultTemplate['message'] }}</textarea>
                            <input type="hidden" name="result" value="{{ $result }}">
                            <button class="button {{ in_array($result, ['no_answer', 'not_interested'], true) ? 'secondary' : '' }}" type="submit">{{ $label }}</button>
                        </form>
                    @endforeach
                </div>
                <div class="muted mt-2">عند اختيار "لم يرد" أو "متابعة غدًا" سيتم إنشاء مهمة للغد. وعند اختيار "طلب تفاصيل" سيتم إنشاء مهمة إرسال التفاصيل.</div>
            </div>
        </div>
    </section>

    <section class="detail-grid" data-customer-tab="overview">
        <article class="panel padded-panel">
            <h2>بيانات العميل</h2>
            <dl class="detail-list">
                <div>
                    <dt>الشركة التابعة لنا</dt>
                    <dd>{{ $customer->owningCompany?->name ?: '-' }}</dd>
                </div>
                <div>
                    <dt>شركة العميل</dt>
                    <dd>{{ $customer->company ?: '-' }}</dd>
                </div>
                <div>
                    <dt>الجوال</dt>
                    <dd>{{ $customer->phone ?: '-' }}</dd>
                </div>
                <div>
                    <dt>البريد</dt>
                    <dd>{{ $customer->email ?: '-' }}</dd>
                </div>
                <div>
                    <dt>الحالة</dt>
                    <dd><span class="badge {{ $customer->status }}">{{ $statuses[$customer->status] ?? $customer->status }}</span></dd>
                </div>
                <div>
                    <dt>المصدر</dt>
                    <dd>{{ $customer->source ?: '-' }}</dd>
                </div>
                <div>
                    <dt>الحملة</dt>
                    <dd>{{ $customer->campaign?->name ?: '-' }}</dd>
                </div>
                <div>
                    <dt>مسؤول المتابعة</dt>
                    <dd>{{ $customer->teamMember?->name ?: '-' }}</dd>
                </div>
                <div>
                    <dt>الدورة / الخدمة</dt>
                    <dd>{{ $customer->interest ?: '-' }}</dd>
                </div>
                <div>
                    <dt>مدينة الخدمة</dt>
                    <dd>{{ $customer->service_city ?: '-' }}</dd>
                </div>
                <div>
                    <dt>العنوان</dt>
                    <dd>{{ $customer->address ?: '-' }}</dd>
                </div>
                <div>
                    <dt>رابط التواصل</dt>
                    <dd>
                        @if(filled($customer->social_url))
                            <a href="{{ $customer->social_url }}" target="_blank" rel="noopener">{{ $customer->social_url }}</a>
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>الحالة المالية</dt>
                    <dd>{{ $paymentStatuses[$customer->payment_status] ?? $customer->payment_status }}</dd>
                </div>
                <div>
                    <dt>المبلغ المدفوع</dt>
                    <dd>{{ $customer->paid_amount !== null ? number_format((float) $customer->paid_amount, 2) : '-' }}</dd>
                </div>
                <div>
                    <dt>باقي المستحق</dt>
                    <dd>{{ $customer->remaining_amount !== null ? number_format((float) $customer->remaining_amount, 2) : '-' }}</dd>
                </div>
                <div>
                    <dt>حالة التسجيل / التنفيذ</dt>
                    <dd>{{ $fulfillmentStatuses[$customer->fulfillment_status] ?? $customer->fulfillment_status }}</dd>
                </div>
                <div>
                    <dt>المتابعة القادمة</dt>
                    <dd>{{ $customer->next_follow_up?->format('Y-m-d') ?: '-' }}</dd>
                </div>
            </dl>

            @if ($customer->notes)
                <div class="note-box">{{ $customer->notes }}</div>
            @endif
        </article>

        <article class="form-panel">
            <h2>إضافة متابعة</h2>
            <form method="POST" action="{{ route('customers.follow-ups.store', $customer) }}">
                @csrf
                <div class="form-grid one-column">
                    <div>
                        <label for="type">نوع المتابعة</label>
                        <select id="type" name="type" required>
                            @foreach ($followUpTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="due_at">موعد المتابعة</label>
                        <input id="due_at" type="datetime-local" name="due_at" value="{{ old('due_at') }}">
                        @error('due_at') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="note">ماذا حدث أو ماذا سنفعل؟</label>
                        <textarea id="note" name="note" required>{{ old('note') }}</textarea>
                        @error('note') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button class="button" type="submit">حفظ المتابعة</button>
                </div>
            </form>
        </article>
    </section>

    <section class="detail-grid mt-panel" data-customer-tab="notes">
        <article class="form-panel">
            <h2>إضافة ملاحظة</h2>
            <form method="POST" action="{{ route('customers.notes.store', $customer) }}">
                @csrf
                <div class="form-grid one-column">
                    <div>
                        <label for="note_team_member_id">المسؤول</label>
                        <select id="note_team_member_id" name="team_member_id">
                            <option value="">بدون مسؤول</option>
                            @foreach ($teamMembers as $teamMember)
                                <option value="{{ $teamMember->id }}" @selected((int) old('team_member_id', $customer->team_member_id) === $teamMember->id)>{{ $teamMember->name }}</option>
                            @endforeach
                        </select>
                        @error('team_member_id') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="body">الملاحظة</label>
                        <textarea id="body" name="body" required>{{ old('body') }}</textarea>
                        @error('body') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button class="button" type="submit">حفظ الملاحظة</button>
                </div>
            </form>
        </article>

        <article class="panel">
            <div class="section-head">
                <h2>سجل الملاحظات</h2>
            </div>

            @if ($customer->customerNotes->count())
                <div class="status-list">
                    @foreach ($customer->customerNotes as $note)
                        <div class="note-box">
                            <div class="actions" style="justify-content: space-between;">
                                <strong>{{ $note->teamMember?->name ?: 'بدون مسؤول' }}</strong>
                                <span class="muted">{{ $note->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            <p style="margin: 10px 0 0;">{{ $note->body }}</p>
                            <form class="form-actions" method="POST" action="{{ route('customer-notes.destroy', $note) }}" onsubmit="return confirm('هل تريد حذف هذه الملاحظة؟')">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">حذف</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">لا توجد ملاحظات لهذا العميل حتى الآن.</div>
            @endif
        </article>
    </section>

    <section class="detail-grid mt-panel" data-customer-tab="work">
        <article class="form-panel">
            <h2>إضافة مهمة</h2>
            <form method="POST" action="{{ route('customers.tasks.store', $customer) }}">
                @csrf
                <div class="form-grid one-column">
                    <div>
                        <label for="task_team_member_id">المسؤول</label>
                        <select id="task_team_member_id" name="team_member_id">
                            <option value="">بدون مسؤول</option>
                            @foreach ($teamMembers as $teamMember)
                                <option value="{{ $teamMember->id }}" @selected((int) old('team_member_id', $customer->team_member_id) === $teamMember->id)>{{ $teamMember->name }}</option>
                            @endforeach
                        </select>
                        @error('team_member_id') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="task_title">عنوان المهمة</label>
                        <input id="task_title" name="title" value="{{ old('title') }}" required>
                        @error('title') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="priority">الأولوية</label>
                        <select id="priority" name="priority" required>
                            @foreach ($taskPriorities as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('priority') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="task_due_at">موعد المهمة</label>
                        <input id="task_due_at" type="datetime-local" name="due_at" value="{{ old('due_at') }}">
                        @error('due_at') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="task_description">التفاصيل</label>
                        <textarea id="task_description" name="description">{{ old('description') }}</textarea>
                        @error('description') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button class="button" type="submit">حفظ المهمة</button>
                </div>
            </form>
        </article>

        <article class="panel">
            <div class="section-head">
                <h2>مهام العميل</h2>
            </div>

            @if ($customer->tasks->count())
                <table>
                    <thead>
                        <tr>
                            <th>المهمة</th>
                            <th>المسؤول</th>
                            <th>الموعد</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customer->tasks as $task)
                            <tr>
                                <td>
                                    <div class="customer-name">{{ $task->title }}</div>
                                    <div class="muted">{{ $task->description ?: '-' }}</div>
                                </td>
                                <td>{{ $task->teamMember?->name ?: '-' }}</td>
                                <td>{{ $task->due_at?->format('Y-m-d H:i') ?: '-' }}</td>
                                <td>
                                    @if ($task->completed_at)
                                        <span class="badge inactive">تمت</span>
                                    @else
                                        <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="button secondary" type="submit">إغلاق</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">لا توجد مهام لهذا العميل حتى الآن.</div>
            @endif
        </article>
    </section>

    <section class="detail-grid mt-panel" data-customer-tab="deals">
        <article class="form-panel">
            <h2>إضافة صفقة</h2>
            <form method="POST" action="{{ route('customers.deals.store', $customer) }}">
                @csrf
                <div class="form-grid one-column">
                    <div>
                        <label for="deal_title">عنوان الصفقة</label>
                        <input id="deal_title" name="title" value="{{ old('title', $customer->interest ?: 'فرصة جديدة') }}" required>
                        @error('title') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="deal_team_member_id">المسؤول</label>
                        <select id="deal_team_member_id" name="team_member_id">
                            <option value="">بدون مسؤول</option>
                            @foreach ($teamMembers as $teamMember)
                                <option value="{{ $teamMember->id }}" @selected((int) old('team_member_id', $customer->team_member_id) === $teamMember->id)>{{ $teamMember->name }}</option>
                            @endforeach
                        </select>
                        @error('team_member_id') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="stage">مرحلة الصفقة</label>
                        <select id="stage" name="stage" required>
                            @foreach ($dealStages as $value => $label)
                                <option value="{{ $value }}" @selected(old('stage', 'new') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('stage') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="amount">القيمة المتوقعة</label>
                        <input id="amount" type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $customer->value) }}">
                        @error('amount') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="expected_close_date">تاريخ الإغلاق المتوقع</label>
                        <input id="expected_close_date" type="date" name="expected_close_date" value="{{ old('expected_close_date') }}">
                        @error('expected_close_date') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="deal_notes">ملاحظات الصفقة</label>
                        <textarea id="deal_notes" name="notes">{{ old('notes') }}</textarea>
                        @error('notes') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button class="button" type="submit">حفظ الصفقة</button>
                </div>
            </form>
        </article>

        <article class="panel">
            <div class="section-head">
                <h2>صفقات العميل</h2>
            </div>

            @if ($customer->deals->count())
                <table>
                    <thead>
                        <tr>
                            <th>الصفقة</th>
                            <th>المسؤول</th>
                            <th>القيمة</th>
                            <th>المرحلة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customer->deals as $deal)
                            <tr>
                                <td>
                                    <div class="customer-name">{{ $deal->title }}</div>
                                    <div class="muted">{{ $deal->expected_close_date?->format('Y-m-d') ?: '-' }}</div>
                                </td>
                                <td>{{ $deal->teamMember?->name ?: '-' }}</td>
                                <td>{{ number_format((float) $deal->amount, 2) }}</td>
                                <td><span class="badge">{{ $dealStages[$deal->stage] ?? $deal->stage }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">لا توجد صفقات لهذا العميل حتى الآن.</div>
            @endif
        </article>
    </section>

    <section class="panel mt-panel" data-customer-tab="work">
        <div class="section-head">
            <h2>سجل المتابعات</h2>
        </div>

        @if ($customer->followUps->count())
            <table>
                <thead>
                    <tr>
                        <th>النوع</th>
                        <th>الملاحظة</th>
                        <th>الموعد</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customer->followUps as $followUp)
                        <tr>
                            <td>{{ $followUpTypes[$followUp->type] ?? $followUp->type }}</td>
                            <td>{{ $followUp->note }}</td>
                            <td>{{ $followUp->due_at?->format('Y-m-d H:i') ?: '-' }}</td>
                            <td>
                                @if ($followUp->completed_at)
                                    <span class="badge inactive">تمت</span>
                                @else
                                    <form method="POST" action="{{ route('follow-ups.complete', $followUp) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="button secondary" type="submit">إغلاق</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد متابعات لهذا العميل حتى الآن.</div>
        @endif
    </section>

    <section class="panel mt-panel" data-customer-tab="notes">
        <div class="section-head">
            <h2>سجل النشاط</h2>
        </div>

        @if ($customer->activities->count())
            <div class="timeline">
                @foreach ($customer->activities as $activity)
                    <div class="timeline-item">
                        <div class="timeline-time">{{ $activity->created_at->format('Y-m-d H:i') }}</div>
                        <div class="timeline-body">
                            <span class="badge">{{ $activity->title }}</span>
                            @if ($activity->description)
                                <p>{{ $activity->description }}</p>
                            @endif
                            <div class="muted">{{ $activity->teamMember?->name ?: 'بدون مسؤول محدد' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty">لا يوجد نشاط مسجل لهذا العميل حتى الآن.</div>
        @endif
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabButtons = Array.from(document.querySelectorAll('[data-customer-tab-button]'));
            const tabSections = Array.from(document.querySelectorAll('[data-customer-tab]'));

            if (!tabButtons.length || !tabSections.length) {
                return;
            }

            const availableTabs = tabButtons.map((button) => button.dataset.customerTabButton);
            const requestedTab = window.location.hash.replace('#', '');
            const defaultTab = availableTabs.includes(requestedTab) ? requestedTab : 'overview';

            const activateTab = (tabName) => {
                tabButtons.forEach((button) => {
                    button.classList.toggle('active', button.dataset.customerTabButton === tabName);
                });

                tabSections.forEach((section) => {
                    section.hidden = section.dataset.customerTab !== tabName;
                });

                if (window.location.hash.replace('#', '') !== tabName) {
                    history.replaceState(null, '', `#${tabName}`);
                }
            };

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => activateTab(button.dataset.customerTabButton));
            });

            activateTab(defaultTab);
        });

        document.addEventListener('DOMContentLoaded', () => {
            const center = document.querySelector('[data-communication-center]');

            if (!center) {
                return;
            }

            const templateSelect = document.getElementById('communication_template');
            const channelSelect = document.getElementById('communication_channel');
            const messageField = document.getElementById('communication_message');
            const whatsappButton = document.getElementById('communication_whatsapp_button');
            const copyButton = document.getElementById('copy_communication_message');
            const whatsappPhone = center.dataset.whatsappPhone;
            const resultForms = center.querySelectorAll('.communication-result-form');

            const syncForms = () => {
                resultForms.forEach((form) => {
                    form.querySelector('.communication-channel-input').value = channelSelect.value;
                    form.querySelector('.communication-template-input').value = templateSelect.value;
                    form.querySelector('.communication-message-input').value = messageField.value;
                });
            };

            const updateWhatsappLink = () => {
                if (!whatsappButton || !whatsappPhone) {
                    return;
                }

                whatsappButton.href = `https://wa.me/${whatsappPhone}?text=${encodeURIComponent(messageField.value)}`;
            };

            const syncCommunication = () => {
                updateWhatsappLink();
                syncForms();
            };

            templateSelect.addEventListener('change', () => {
                const selectedOption = templateSelect.options[templateSelect.selectedIndex];
                messageField.value = selectedOption.dataset.message || '';
                syncCommunication();
            });

            channelSelect.addEventListener('change', syncForms);
            messageField.addEventListener('input', syncCommunication);

            center.querySelectorAll('[data-channel]').forEach((button) => {
                button.addEventListener('click', () => {
                    channelSelect.value = button.dataset.channel;
                    syncForms();
                });
            });

            copyButton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(messageField.value);
                    copyButton.textContent = 'تم النسخ';
                    setTimeout(() => copyButton.textContent = 'نسخ الرسالة', 1600);
                } catch (error) {
                    messageField.select();
                    document.execCommand('copy');
                }
            });

            syncCommunication();
        });
    </script>
@endsection
