<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::where('email', 'admin@crm.local')->first();
        $superAdminRole = Role::where('slug', 'super_admin')->first();

        if ($admin && $superAdminRole) {
            $admin->update([
                'role_id' => $superAdminRole->id,
                'is_super_admin' => true,
            ]);
        }

        $this->actingAs($admin);
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/customers');

        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        auth()->logout();

        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_login_and_logout(): void
    {
        auth()->logout();

        $loginResponse = $this->post('/login', [
            'email' => 'admin@crm.local',
            'password' => 'admin12345',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $logoutResponse = $this->post('/logout');

        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_change_own_password(): void
    {
        $user = User::where('email', 'admin@crm.local')->first();

        $this->get('/profile')
            ->assertOk()
            ->assertSee('البروفايل');

        $response = $this->put('/profile/password', [
            'current_password' => 'admin12345',
            'password' => 'new-admin-password',
            'password_confirmation' => 'new-admin-password',
        ]);

        $response->assertRedirect('/profile');
        $this->assertFalse(auth()->attempt([
            'email' => $user->email,
            'password' => 'admin12345',
        ]));
        $this->assertTrue(auth()->attempt([
            'email' => $user->email,
            'password' => 'new-admin-password',
        ]));
    }

    public function test_sales_user_cannot_delete_customers(): void
    {
        $company = Company::first();
        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Protected Customer',
            'status' => 'lead',
        ]);
        $salesUser = User::create([
            'name' => 'Sales User',
            'email' => 'sales@example.com',
            'password' => 'password',
            'role' => 'sales',
        ]);

        $response = $this->actingAs($salesUser)->delete("/customers/{$customer->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Protected Customer',
        ]);
    }

    public function test_manager_can_manage_system_users_and_sales_cannot(): void
    {
        $response = $this->post('/users', [
            'name' => 'New Sales',
            'email' => 'new-sales@example.com',
            'password' => 'password123',
            'role' => 'sales',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'new-sales@example.com',
            'role' => 'sales',
        ]);

        $salesUser = User::where('email', 'new-sales@example.com')->first();
        $this->actingAs($salesUser)->get('/users')->assertForbidden();
    }

    public function test_company_limited_user_only_sees_assigned_company_customers(): void
    {
        $beit = Company::where('name', 'بيت المصور')->first();
        $vida = Company::where('name', 'فيدا برودكشن')->first();

        $beitCustomer = Customer::create([
            'company_id' => $beit->id,
            'name' => 'Beit Visible Customer',
            'status' => 'lead',
        ]);
        $vidaCustomer = Customer::create([
            'company_id' => $vida->id,
            'name' => 'Vida Hidden Customer',
            'status' => 'lead',
        ]);
        $user = User::create([
            'name' => 'Beit Only',
            'email' => 'beit-only@example.com',
            'password' => 'password',
            'role' => 'sales',
            'role_id' => Role::where('slug', 'sales')->value('id'),
        ]);
        $user->companies()->attach($beit->id);

        $this->actingAs($user)->get('/customers')
            ->assertOk()
            ->assertSee($beitCustomer->name)
            ->assertDontSee($vidaCustomer->name);

        $this->actingAs($user)->get("/customers/{$vidaCustomer->id}")
            ->assertForbidden();
    }

    public function test_granular_role_can_create_customers_without_update_permission(): void
    {
        $company = Company::first();
        $role = Role::create([
            'name' => 'Create Only',
            'slug' => 'create_only',
            'description' => 'Can create customers without editing existing records.',
        ]);
        $role->permissions()->sync(Permission::whereIn('key', [
            'customers.view',
            'customers.create',
        ])->pluck('id'));

        $user = User::create([
            'name' => 'Create Only User',
            'email' => 'create-only@example.com',
            'password' => 'password',
            'role' => 'sales',
            'role_id' => $role->id,
        ]);
        $user->companies()->attach($company->id);

        $this->actingAs($user)->post('/customers', [
            'company_id' => $company->id,
            'name' => 'Created By Limited Role',
            'status' => 'lead',
        ])->assertRedirect('/customers');

        $customer = Customer::where('name', 'Created By Limited Role')->first();
        $this->assertNotNull($customer);

        $this->actingAs($user)->get("/customers/{$customer->id}/edit")
            ->assertForbidden();
    }

    public function test_customers_can_be_imported_from_csv(): void
    {
        $csv = "\xEF\xBB\xBFاسم العميل,رقم الجوال,البريد الإلكتروني,الشركة التابعة لنا,المصدر,الدورة المطلوبة,الحالة,مدينة الخدمة,العنوان\n";
        $csv .= "عميل تجربة,0501234567,test-import@example.com,بيت المصور,إعلان جوجل,اساسيات التصوير,عميل محتمل,جدة,حي السلامة\n";

        $response = $this->post('/customers/import', [
            'default_company_id' => 1,
            'customers_file' => UploadedFile::fake()->createWithContent('customers.csv', $csv),
        ]);

        $response->assertRedirect('/customers');

        $this->assertDatabaseHas('customers', [
            'name' => 'عميل تجربة',
            'phone' => '0501234567',
            'email' => 'test-import@example.com',
            'interest' => 'اساسيات التصوير',
            'service_city' => 'جدة',
            'address' => 'حي السلامة',
            'status' => 'lead',
        ]);
    }

    public function test_customer_status_can_be_updated_from_pipeline(): void
    {
        $company = Company::first();
        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Pipeline Test',
            'status' => 'lead',
        ]);

        $response = $this->patch("/pipeline/customers/{$customer->id}/status", [
            'status' => 'contacted',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => 'contacted',
        ]);
        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'type' => 'status_changed',
            'title' => 'تم نقل العميل في المسار',
        ]);
    }

    public function test_pipeline_can_be_filtered_by_status_interest_company_city_and_owner(): void
    {
        $beit = Company::where('name', 'بيت المصور')->first();
        $vida = Company::where('name', 'فيدا برودكشن')->first();
        $teamMember = TeamMember::create([
            'name' => 'Pipeline Owner',
            'is_active' => true,
        ]);

        Customer::create([
            'company_id' => $beit->id,
            'team_member_id' => $teamMember->id,
            'name' => 'Filtered Pipeline Customer',
            'status' => 'prospect',
            'interest' => 'اساسيات التصوير',
            'service_city' => 'جدة',
        ]);

        Customer::create([
            'company_id' => $vida->id,
            'name' => 'Other Pipeline Customer',
            'status' => 'lead',
            'interest' => 'تصوير منتجات',
            'service_city' => 'الرياض',
        ]);

        $response = $this->get('/pipeline?' . http_build_query([
            'company_id' => $beit->id,
            'status' => 'prospect',
            'interest' => 'اساسيات التصوير',
            'service_city' => 'جدة',
            'team_member_id' => $teamMember->id,
        ]));

        $response->assertOk();
        $response->assertSee('Filtered Pipeline Customer');
        $response->assertDontSee('Other Pipeline Customer');
        $response->assertSee('عدد العملاء المطابقين');
    }

    public function test_follow_up_center_loads_open_follow_ups(): void
    {
        $company = Company::first();
        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Follow Up Test',
            'status' => 'lead',
        ]);

        FollowUp::create([
            'customer_id' => $customer->id,
            'type' => 'call',
            'note' => 'Call student today',
            'due_at' => now(),
        ]);

        $response = $this->get('/follow-ups/today');

        $response->assertStatus(200);
        $response->assertSee('Call student today');
    }

    public function test_duplicates_report_shows_repeated_phone_numbers(): void
    {
        $company = Company::first();

        Customer::create([
            'company_id' => $company->id,
            'name' => 'Duplicate One',
            'phone' => '0509999999',
            'status' => 'lead',
        ]);

        Customer::create([
            'company_id' => $company->id,
            'name' => 'Duplicate Two',
            'phone' => '0509999999',
            'status' => 'contacted',
        ]);

        $response = $this->get('/reports/duplicates');

        $response->assertStatus(200);
        $response->assertSee('0509999999');
    }

    public function test_campaigns_can_be_created_and_reported(): void
    {
        $company = Company::first();

        $campaign = Campaign::create([
            'company_id' => $company->id,
            'name' => 'Google Test Campaign',
            'channel' => 'Google Ads',
            'is_active' => true,
        ]);

        Customer::create([
            'company_id' => $company->id,
            'campaign_id' => $campaign->id,
            'name' => 'Campaign Customer',
            'status' => 'customer',
        ]);

        $response = $this->get('/reports/campaigns');

        $response->assertStatus(200);
        $response->assertSee('Google Test Campaign');
        $response->assertSee('100%');
    }

    public function test_customer_can_be_assigned_to_team_member(): void
    {
        $company = Company::first();
        $teamMember = TeamMember::create([
            'name' => 'Sales Owner',
            'role' => 'متابعة',
            'is_active' => true,
        ]);

        $response = $this->post('/customers', [
            'company_id' => $company->id,
            'team_member_id' => $teamMember->id,
            'name' => 'Assigned Customer',
            'status' => 'lead',
            'service_city' => 'جدة',
            'address' => 'حي الروضة',
        ]);

        $response->assertRedirect('/customers');

        $this->assertDatabaseHas('customers', [
            'name' => 'Assigned Customer',
            'team_member_id' => $teamMember->id,
            'service_city' => 'جدة',
            'address' => 'حي الروضة',
        ]);
    }

    public function test_team_report_shows_assigned_customers(): void
    {
        $company = Company::first();
        $teamMember = TeamMember::create([
            'name' => 'Team Report Owner',
            'role' => 'مبيعات',
            'is_active' => true,
        ]);

        Customer::create([
            'company_id' => $company->id,
            'team_member_id' => $teamMember->id,
            'name' => 'Team Report Customer',
            'status' => 'customer',
        ]);

        $response = $this->get('/reports/team');

        $response->assertStatus(200);
        $response->assertSee('Team Report Owner');
        $response->assertSee('100%');
    }

    public function test_customer_notes_can_be_added(): void
    {
        $company = Company::first();
        $teamMember = TeamMember::create([
            'name' => 'Note Owner',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'company_id' => $company->id,
            'team_member_id' => $teamMember->id,
            'name' => 'Note Customer',
            'status' => 'lead',
        ]);

        $response = $this->post("/customers/{$customer->id}/notes", [
            'team_member_id' => $teamMember->id,
            'body' => 'Customer asked for details',
        ]);

        $response->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('customer_notes', [
            'customer_id' => $customer->id,
            'team_member_id' => $teamMember->id,
            'body' => 'Customer asked for details',
        ]);
        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'team_member_id' => $teamMember->id,
            'type' => 'note_added',
        ]);
    }

    public function test_communication_center_records_result_and_creates_follow_up_task(): void
    {
        $company = Company::first();
        $teamMember = TeamMember::create([
            'name' => 'Communication Owner',
            'is_active' => true,
        ]);
        $customer = Customer::create([
            'company_id' => $company->id,
            'team_member_id' => $teamMember->id,
            'name' => 'Communication Customer',
            'phone' => '0501234567',
            'status' => 'lead',
            'interest' => 'اساسيات التصوير',
            'service_city' => 'جدة',
        ]);

        $this->get("/customers/{$customer->id}")
            ->assertOk()
            ->assertSee('مركز التواصل')
            ->assertSee('فتح واتساب');

        $response = $this->post("/customers/{$customer->id}/communication", [
            'channel' => 'whatsapp',
            'result' => 'no_answer',
            'template_key' => 'new_lead',
            'message' => 'رسالة متابعة جاهزة',
        ]);

        $response->assertRedirect("/customers/{$customer->id}");

        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'type' => 'communication_result',
            'title' => 'تم تسجيل نتيجة التواصل',
        ]);
        $this->assertDatabaseHas('customer_tasks', [
            'customer_id' => $customer->id,
            'team_member_id' => $teamMember->id,
            'title' => 'متابعة بعد عدم الرد',
            'priority' => 'normal',
        ]);
        $this->assertNotNull($customer->fresh()->next_follow_up);
    }

    public function test_lighting_course_details_template_is_shown_for_interested_customer(): void
    {
        $company = Company::where('name', 'بيت المصور')->first() ?: Company::first();
        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Lighting Customer',
            'phone' => '0501234567',
            'status' => 'prospect',
            'interest' => 'احتراف الاضاءة',
            'service_city' => 'جدة',
        ]);

        $this->get("/customers/{$customer->id}")
            ->assertOk()
            ->assertSee('ورشة احتراف الإضاءة الشاملة')
            ->assertSee('قيمة الورشة: 1800 بدلًا من 2500 ريال لفترة محدودة')
            ->assertSee('مع أحمد زغلول');
    }

    public function test_deposit_customer_appears_in_recommendations_and_alerts(): void
    {
        $company = Company::first();
        $company->update(['name' => 'بيت المصور']);
        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Deposit Recommendation Customer',
            'status' => 'prospect',
            'value' => 1000,
            'payment_status' => 'deposit',
            'paid_amount' => 300,
            'fulfillment_status' => 'pending',
        ]);

        $this->get("/customers/{$customer->id}")
            ->assertOk()
            ->assertSee('توصية النظام')
            ->assertSee('طالب بباقي المستحق');

        $this->get('/alerts')
            ->assertOk()
            ->assertSee('Deposit Recommendation Customer')
            ->assertSee('طالب بباقي المستحق');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('توصيات المتابعة')
            ->assertSee('Deposit Recommendation Customer');
    }

    public function test_recommendation_action_can_create_collection_task(): void
    {
        $company = Company::first();
        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Collection Action Customer',
            'status' => 'prospect',
            'value' => 1200,
            'payment_status' => 'deposit',
            'paid_amount' => 400,
            'fulfillment_status' => 'pending',
        ]);

        $this->post("/customers/{$customer->id}/recommendations", [
            'action' => 'collect_remaining',
        ])->assertRedirect();

        $this->assertDatabaseHas('customer_tasks', [
            'customer_id' => $customer->id,
            'title' => 'تحصيل باقي المستحق',
            'priority' => 'high',
        ]);
        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'type' => 'recommendation_action',
            'title' => 'تم تنفيذ توصية النظام',
        ]);
    }

    public function test_recommendation_action_can_mark_paid_beit_customer_registered(): void
    {
        $company = Company::first();
        $company->update(['name' => 'بيت المصور']);
        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Registered Action Customer',
            'status' => 'prospect',
            'payment_status' => 'paid',
            'paid_amount' => 1000,
            'fulfillment_status' => 'pending',
        ]);

        $this->post("/customers/{$customer->id}/recommendations", [
            'action' => 'mark_registered',
        ])->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => 'customer',
            'fulfillment_status' => 'registered',
        ]);
    }

    public function test_customer_tasks_can_be_added_and_completed(): void
    {
        $company = Company::first();
        $teamMember = TeamMember::create([
            'name' => 'Task Owner',
            'is_active' => true,
        ]);
        $customer = Customer::create([
            'company_id' => $company->id,
            'team_member_id' => $teamMember->id,
            'name' => 'Task Customer',
            'status' => 'lead',
        ]);

        $response = $this->post("/customers/{$customer->id}/tasks", [
            'team_member_id' => $teamMember->id,
            'title' => 'Send course offer',
            'description' => 'Send price and schedule',
            'priority' => 'high',
            'due_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect("/customers/{$customer->id}");
        $this->assertDatabaseHas('customer_tasks', [
            'customer_id' => $customer->id,
            'team_member_id' => $teamMember->id,
            'title' => 'Send course offer',
            'priority' => 'high',
        ]);

        $taskId = $customer->tasks()->where('title', 'Send course offer')->value('id');
        $this->get('/tasks/today')->assertOk()->assertSee('Send course offer');

        $this->patch("/tasks/{$taskId}/complete")->assertRedirect();
        $this->assertNotNull($customer->tasks()->whereKey($taskId)->value('completed_at'));
        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'type' => 'task_completed',
            'title' => 'تم إغلاق مهمة',
        ]);
    }

    public function test_deals_can_be_added_and_moved_to_won(): void
    {
        $company = Company::first();
        $teamMember = TeamMember::create([
            'name' => 'Deal Owner',
            'is_active' => true,
        ]);
        $customer = Customer::create([
            'company_id' => $company->id,
            'team_member_id' => $teamMember->id,
            'name' => 'Deal Customer',
            'status' => 'prospect',
        ]);

        $response = $this->post("/customers/{$customer->id}/deals", [
            'team_member_id' => $teamMember->id,
            'title' => 'Photography Course Deal',
            'stage' => 'proposal',
            'amount' => 1500,
            'expected_close_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect("/customers/{$customer->id}");
        $this->assertDatabaseHas('deals', [
            'customer_id' => $customer->id,
            'team_member_id' => $teamMember->id,
            'title' => 'Photography Course Deal',
            'stage' => 'proposal',
        ]);

        $dealId = $customer->deals()->where('title', 'Photography Course Deal')->value('id');
        $this->get('/deals')->assertOk()->assertSee('Photography Course Deal');

        $this->patch("/deals/{$dealId}/stage", [
            'stage' => 'won',
        ])->assertRedirect();

        $this->assertDatabaseHas('deals', [
            'id' => $dealId,
            'stage' => 'won',
        ]);
        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'type' => 'deal_stage_changed',
            'title' => 'تم تغيير مرحلة الصفقة',
        ]);
    }

    public function test_alerts_page_collects_due_work(): void
    {
        $company = Company::first();
        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Alert Customer',
            'status' => 'prospect',
        ]);

        FollowUp::create([
            'customer_id' => $customer->id,
            'type' => 'call',
            'note' => 'Alert follow up',
            'due_at' => now()->subHour(),
        ]);
        $customer->tasks()->create([
            'title' => 'Alert task',
            'priority' => 'normal',
            'due_at' => now()->subHour(),
        ]);
        $customer->deals()->create([
            'company_id' => $company->id,
            'title' => 'Alert deal',
            'stage' => 'proposal',
            'amount' => 500,
            'expected_close_date' => now()->subDay()->toDateString(),
        ]);

        $response = $this->get('/alerts');

        $response->assertOk();
        $response->assertSee('Alert follow up');
        $response->assertSee('Alert task');
        $response->assertSee('Alert deal');
    }

    public function test_manager_can_export_customers_and_deals(): void
    {
        $company = Company::first();
        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => 'Export Customer',
            'status' => 'lead',
        ]);
        $customer->deals()->create([
            'company_id' => $company->id,
            'title' => 'Export Deal',
            'stage' => 'new',
            'amount' => 250,
        ]);

        $customersExport = $this->get('/exports/customers');
        $dealsExport = $this->get('/exports/deals');

        $customersExport->assertOk();
        $dealsExport->assertOk();
        $this->assertStringContainsString('Export Customer', $customersExport->streamedContent());
        $this->assertStringContainsString('Export Deal', $dealsExport->streamedContent());
    }

    public function test_customer_creation_is_added_to_activity_log(): void
    {
        $company = Company::first();

        $response = $this->post('/customers', [
            'company_id' => $company->id,
            'name' => 'Activity Customer',
            'status' => 'lead',
        ]);

        $response->assertRedirect('/customers');

        $customer = Customer::where('name', 'Activity Customer')->first();
        $this->assertNotNull($customer);
        $this->assertDatabaseHas('customer_activities', [
            'customer_id' => $customer->id,
            'type' => 'created',
            'title' => 'تم إضافة العميل',
        ]);
    }
}
