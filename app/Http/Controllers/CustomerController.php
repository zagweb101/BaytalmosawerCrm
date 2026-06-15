<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Company;
use App\Models\CompanyOffering;
use App\Models\Customer;
use App\Models\TeamMember;
use App\Services\RecommendationService;
use App\Support\CustomerStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query()->with(['owningCompany', 'campaign', 'teamMember'])->latest();
        $this->applyCompanyScope($query);

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($customers) use ($search) {
                $customers
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('interest', 'like', "%{$search}%")
                    ->orWhereHas('campaign', fn ($campaign) => $campaign->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($source = $request->string('source')->trim()->toString()) {
            $query->where('source', $source);
        }

        if ($interest = $request->string('interest')->trim()->toString()) {
            $query->where('interest', $interest);
        }

        if ($campaignId = $request->integer('campaign_id')) {
            $query->where('campaign_id', $campaignId);
        }

        if ($teamMemberId = $request->integer('team_member_id')) {
            $query->where('team_member_id', $teamMemberId);
        }

        if ($companyId = $request->integer('company_id')) {
            $query->where('company_id', $companyId);
        }

        return view('customers.index', [
            'customers' => $query->paginate(10)->withQueryString(),
            'companies' => $this->visibleCompanies(),
            'campaigns' => $this->applyCompanyScope(Campaign::query()->with('company'))->orderBy('name')->get(),
            'teamMembers' => TeamMember::where('is_active', true)->orderBy('name')->get(),
            'sources' => $this->applyCompanyScope(Customer::query())
                ->whereNotNull('source')
                ->where('source', '<>', '')
                ->distinct()
                ->orderBy('source')
                ->pluck('source'),
            'interests' => $this->applyCompanyScope(Customer::query())
                ->whereNotNull('interest')
                ->where('interest', '<>', '')
                ->distinct()
                ->orderBy('interest')
                ->pluck('interest'),
            'statuses' => CustomerStatus::orderedLabelsFor(),
            'totalCustomers' => $this->applyCompanyScope(Customer::query())->count(),
            'activeCustomers' => $this->applyCompanyScope(Customer::query())->where('status', 'customer')->count(),
            'openLeads' => $this->applyCompanyScope(Customer::query())->whereIn('status', ['lead', 'contacted', 'prospect'])->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $companies = $this->visibleCompanies();
        $requestedCompanyId = $request->integer('company_id') ?: null;

        if ($requestedCompanyId) {
            $this->ensureCompanyAccess($requestedCompanyId);
        }

        $customerCompanyId = $requestedCompanyId ?: $companies->first()?->id;
        $companyName = $companies->firstWhere('id', $customerCompanyId)?->name;

        return view('customers.create', [
            'customer' => new Customer([
                'company_id' => $customerCompanyId,
                'status' => 'lead',
            ]),
            'companies' => $companies,
            'campaigns' => $this->applyCompanyScope(Campaign::with('company'))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'teamMembers' => TeamMember::where('is_active', true)->orderBy('name')->get(),
            'courseOptions' => $this->applyCompanyScope(CompanyOffering::query())
                ->where('type', 'course')
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name'),
            'serviceCities' => $this->serviceCities(),
            'statuses' => CustomerStatus::orderedLabelsFor($companyName),
            'beitStatuses' => CustomerStatus::orderedLabelsFor(CustomerStatus::BEIT_COMPANY),
            'vidaStatuses' => CustomerStatus::orderedLabelsFor(CustomerStatus::VIDA_COMPANY),
            'paymentStatuses' => $this->paymentStatuses(),
            'fulfillmentStatuses' => $this->fulfillmentStatuses(),
        ]);
    }

    public function show(Customer $customer): View
    {
        $this->ensureCompanyAccess($customer->company_id);

        $customer->load(['owningCompany', 'campaign', 'teamMember', 'followUps', 'customerNotes.teamMember', 'tasks.teamMember', 'deals.teamMember', 'activities.teamMember']);

        return view('customers.show', [
            'customer' => $customer,
            'teamMembers' => TeamMember::where('is_active', true)->orderBy('name')->get(),
            'statuses' => CustomerStatus::labelsFor($customer->owningCompany?->name),
            'followUpTypes' => $this->followUpTypes(),
            'taskPriorities' => $this->taskPriorities(),
            'dealStages' => $this->dealStages(),
            'serviceCities' => $this->serviceCities(),
            'communicationCenter' => $this->communicationCenter($customer),
            'paymentStatuses' => $this->paymentStatuses(),
            'fulfillmentStatuses' => $this->fulfillmentStatuses(),
            'recommendations' => app(RecommendationService::class)->forCustomer($customer),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->ensureCompanyAccess((int) $data['company_id']);
        $this->ensureCustomerRelations($data);

        $customer = Customer::create($data);
        $customer->recordActivity('created', 'تم إضافة العميل', 'تم إنشاء ملف العميل في النظام.');

        return redirect()->route('customers.index')->with('success', 'تمت إضافة العميل بنجاح.');
    }

    public function edit(Customer $customer): View
    {
        $this->ensureCompanyAccess($customer->company_id);
        $customer->loadMissing('owningCompany');
        $companies = $this->visibleCompanies();

        return view('customers.edit', [
            'customer' => $customer,
            'companies' => $companies,
            'campaigns' => $this->applyCompanyScope(Campaign::with('company'))
                ->where(function ($query) use ($customer) {
                    $query->where('is_active', true)
                        ->orWhere('id', $customer->campaign_id);
                })
                ->orderBy('name')
                ->get(),
            'teamMembers' => TeamMember::query()
                ->where('is_active', true)
                ->orWhere('id', $customer->team_member_id)
                ->orderBy('name')
                ->get(),
            'courseOptions' => $this->applyCompanyScope(CompanyOffering::query())
                ->where('type', 'course')
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name'),
            'serviceCities' => $this->serviceCities(),
            'statuses' => CustomerStatus::orderedLabelsFor($customer->owningCompany?->name),
            'beitStatuses' => CustomerStatus::orderedLabelsFor(CustomerStatus::BEIT_COMPANY),
            'vidaStatuses' => CustomerStatus::orderedLabelsFor(CustomerStatus::VIDA_COMPANY),
            'paymentStatuses' => $this->paymentStatuses(),
            'fulfillmentStatuses' => $this->fulfillmentStatuses(),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);

        $oldStatus = $customer->status;
        $data = $this->validatedData($request);
        $this->ensureCompanyAccess((int) $data['company_id']);
        $this->ensureCustomerRelations($data);

        $customer->update($data);

        if ($oldStatus !== $customer->status) {
            $customer->loadMissing('owningCompany');
            $statuses = CustomerStatus::labelsFor($customer->owningCompany?->name);
            $customer->recordActivity(
                'status_changed',
                'تم تغيير حالة العميل',
                'من ' . ($statuses[$oldStatus] ?? $oldStatus) . ' إلى ' . ($statuses[$customer->status] ?? $customer->status),
                metadata: [
                    'from' => $oldStatus,
                    'to' => $customer->status,
                ],
            );
        }

        return redirect()->route('customers.index')->with('success', 'تم تحديث بيانات العميل.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->ensureCompanyAccess($customer->company_id);

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'تم حذف العميل.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'team_member_id' => ['nullable', 'exists:team_members,id'],
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', CustomerStatus::validationRule()],
            'source' => ['nullable', 'string', 'max:255'],
            'interest' => ['nullable', 'string', 'max:255'],
            'service_city' => ['nullable', 'in:' . implode(',', array_keys($this->serviceCities()))],
            'social_url' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['nullable', 'in:' . implode(',', array_keys($this->paymentStatuses()))],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'fulfillment_status' => ['nullable', 'in:' . implode(',', array_keys($this->fulfillmentStatuses()))],
            'next_follow_up' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]) + [
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'pending',
        ];
    }

    private function ensureCustomerRelations(array $data): void
    {
        if (! empty($data['campaign_id'])) {
            $campaign = Campaign::findOrFail($data['campaign_id']);
            $this->ensureCompanyAccess($campaign->company_id);

            if ((int) $campaign->company_id !== (int) $data['company_id']) {
                abort(422, 'الحملة المختارة لا تتبع نفس شركة العميل.');
            }
        }
    }

    private function followUpTypes(): array
    {
        return [
            'call' => 'اتصال',
            'whatsapp' => 'واتساب',
            'email' => 'بريد إلكتروني',
            'meeting' => 'اجتماع',
            'other' => 'أخرى',
        ];
    }

    private function taskPriorities(): array
    {
        return [
            'low' => 'منخفضة',
            'normal' => 'عادية',
            'high' => 'عاجلة',
        ];
    }

    private function paymentStatuses(): array
    {
        return [
            'unpaid' => 'لم يدفع',
            'deposit' => 'دفع عربون',
            'paid' => 'مكتمل الدفع',
            'refunded' => 'مسترد / ملغي',
        ];
    }

    private function fulfillmentStatuses(): array
    {
        return [
            'pending' => 'لم يبدأ',
            'registered' => 'تم التسجيل',
            'in_progress' => 'جاري التنفيذ',
            'delivered' => 'تم تنفيذ الخدمة',
            'completed' => 'مكتمل',
        ];
    }

    private function serviceCities(): array
    {
        return [
            'جدة' => 'جدة',
            'الرياض' => 'الرياض',
            'الدمام' => 'الدمام',
            'مكة' => 'مكة',
            'المدينة' => 'المدينة',
            'الطائف' => 'الطائف',
            'القاهرة' => 'القاهرة',
            'الاسكندرية' => 'الاسكندرية',
        ];
    }

    private function dealStages(): array
    {
        return [
            'new' => 'فرصة جديدة',
            'proposal' => 'عرض سعر / تفاصيل',
            'negotiation' => 'تفاوض',
            'won' => 'تم الفوز',
            'lost' => 'مغلقة / خاسرة',
        ];
    }

    private function communicationCenter(Customer $customer): array
    {
        $brandWhatsapp = '966533515176';
        $brandName = $customer->owningCompany?->name ?: 'فريق المتابعة';
        $interest = $customer->interest ?: 'الخدمة المطلوبة';
        $city = $customer->service_city ?: 'مدينتك';
        $sendDetailsMessage = $this->sendDetailsMessage($customer, $brandWhatsapp, $interest);

        return [
            'customer_whatsapp_phone' => $this->normalizePhoneForWhatsapp($customer->phone),
            'customer_tel' => $this->normalizePhoneForTel($customer->phone),
            'brand_whatsapp_phone' => $brandWhatsapp,
            'instagram_url' => $this->brandInstagramUrl($customer->owningCompany?->name),
            'channels' => [
                'whatsapp' => 'واتساب',
                'call' => 'اتصال',
                'instagram' => 'إنستغرام',
                'other' => 'أخرى',
            ],
            'results' => [
                'contacted' => 'تم التواصل',
                'no_answer' => 'لم يرد',
                'requested_details' => 'طلب تفاصيل',
                'booked' => 'حجز',
                'not_interested' => 'غير مهتم',
                'follow_up_tomorrow' => 'متابعة غدًا',
            ],
            'templates' => [
                'new_lead' => [
                    'label' => 'عميل جديد',
                    'message' => "مرحبًا {$customer->name}، معك {$brandName}.\nوصلنا استفسارك بخصوص {$interest} في {$city}، ويسعدنا نساعدك بالتفاصيل المناسبة.\nهل يناسبك أرسل لك التفاصيل الآن؟\n\nللتواصل: {$brandWhatsapp}",
                ],
                'interested' => [
                    'label' => 'مهتم بالدورة أو الخدمة',
                    'message' => "أهلًا {$customer->name}، بخصوص {$interest}.\nأقدر أرسل لك المواعيد والتفاصيل وطريقة الحجز الآن، ولو عندك سؤال محدد اكتبه لي وسأرد عليك مباشرة.\n\nللتواصل: {$brandWhatsapp}",
                ],
                'booking_reminder' => [
                    'label' => 'تذكير بالحجز',
                    'message' => "مرحبًا {$customer->name}، تذكير بسيط بخصوص حجز {$interest}.\nلو ما زلت مهتمًا، أرسل لك خطوات الحجز المتاحة الآن.\n\nللتواصل: {$brandWhatsapp}",
                ],
                'send_details' => [
                    'label' => 'إرسال تفاصيل',
                    'message' => $sendDetailsMessage,
                ],
                'no_reply' => [
                    'label' => 'متابعة بعد عدم الرد',
                    'message' => "مرحبًا {$customer->name}، حاولنا التواصل معك بخصوص {$interest}.\nعندما يناسبك، أرسل لنا الوقت المناسب أو سؤالك وسنساعدك مباشرة.\n\nللتواصل: {$brandWhatsapp}",
                ],
            ],
        ];
    }

    private function sendDetailsMessage(Customer $customer, string $brandWhatsapp, string $interest): string
    {
        if (str_contains($this->normalizeArabicText($interest), 'احتراف الاضاءة')) {
            return "مرحبًا {$customer->name} 👋\n\n🎬 ورشة احتراف الإضاءة الشاملة (حضوري)\n\nمع أحمد زغلول\n\n📅 لمدة 5 أيام\n⏰ الوقت: يوميًا من 6 مساءً إلى 10 مساءً\n📍 المكان: بيت المصور\n\n⚠️ ليه أنت محتاج الورشة دي؟ (المشاكل اللي بتقابلك)\n\n* صورك باهتة أو فيها شادو غلط\n* مش فاهم الفرق بين الإضاءة الناعمة والحادة\n* بتستخدم الإضاءة عشوائي ومش عارف توظفها\n* بتتوه بين أنواع المعدّات ومش فاهم الفرق بينهم\n* الألوان عندك مش مظبوطة أو بتطلع مش واقعية\n* بتعتمد على الفوتوشوب أكتر من اللازم\n\n✅ الحل هنا\n(إيه اللي هتتعلمه فعليًا)\n\nهتتعلم الإضاءة من الصفر لحد الاحتراف بشكل عملي 👇\n\n📌 مصادر الإضاءة وأنواعها\n📌 اتجاهات الإضاءة وتأثيرها على الشكل\n📌 التحكم في شدة الإضاءة\n📌 ربط التريقر بالفلاش بالطريقة الصحيحة\n📌 مشتتات ومشكلات الاضاءة والعواكس واستخدامها الصح (Modifiers)\n📌 فهم عجلة الألوان واختيار ألوان احترافية\n📌 توزيع الإضاءة بشكل سينمائي\n📌 تطبيق عملي بورتريه\n📌 تصوير فاشن + Color Gel\n📌 معالجة الصور باحتراف في الفوتوشوب\n\n🎯 هدف الورشة\nمش بس تفهم الإضاءة…\nلكن تتحكم فيها بالكامل وتقدر تطلع أي لوك في دماغك 👌\n\n🔥 النتيجة بعد الورشة\n* هتصور بأي إضاءة وتطلع شغل احترافي\n* هتفهم الإضاءة بدل ما تحفظ Setup\n* هتقلل اعتمادك على التعديل\n* هتقدر تشتغل مع عملاء بثقة أعلى\n* هتبدأ تطور ستايلك الخاص كمصور\n\n💎 مميزات إضافية\n✨ جروب واتساب دائم مع أحمد زغلول\n✨ متابعة واستفسارات بعد الورشة\n✨ تطبيق عملي حقيقي مش نظري\n\n💰 السعر\n\nقيمة الورشة: 1800 بدلًا من 2500 ريال لفترة محدودة\n\n🚨 متفوتش الفرصة\nالأماكن محدودة…\nولو فعلاً عايز تنقل مستواك نقلة حقيقية في التصوير 👇\n\n📩 احجز مكانك دلوقتي قبل ما يكتمل العدد\n\nللتواصل: {$brandWhatsapp}";
        }

        return "مرحبًا {$customer->name}.\nهذه رسالة متابعة بخصوص {$interest}. سأرسل لك التفاصيل الأساسية: المحتوى، الموعد، السعر، وطريقة الحجز.\n\nللتواصل: {$brandWhatsapp}";
    }

    private function normalizeArabicText(string $text): string
    {
        return str_replace(['أ', 'إ', 'آ', 'ٱ'], 'ا', $text);
    }

    private function normalizePhoneForWhatsapp(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            return '966' . substr($digits, 1);
        }

        if (strlen($digits) === 9 && str_starts_with($digits, '5')) {
            return '966' . $digits;
        }

        return $digits;
    }

    private function normalizePhoneForTel(?string $phone): ?string
    {
        $digits = $this->normalizePhoneForWhatsapp($phone);

        return $digits ? '+' . $digits : null;
    }

    private function brandInstagramUrl(?string $companyName): ?string
    {
        if (! $companyName) {
            return null;
        }

        if (str_contains($companyName, 'فيدا')) {
            return 'https://www.instagram.com/vidaproduction.studio/';
        }

        if (str_contains($companyName, 'بيت المصور')) {
            return 'https://www.instagram.com/baytalmosawer/?hl=ar';
        }

        return null;
    }
}
