<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $manager;

    private User $sales;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::where('slug', 'super_admin')->firstOrFail();
        $managerRole = Role::where('slug', 'manager')->firstOrFail();
        $salesRole = Role::where('slug', 'sales')->firstOrFail();

        $this->superAdmin = User::where('email', 'admin@crm.local')->firstOrFail();
        $this->superAdmin->update([
            'role_id' => $superAdminRole->id,
            'role' => 'manager',
            'is_super_admin' => true,
        ]);

        $this->manager = User::create([
            'name' => 'مدير تجريبي',
            'email' => 'manager-test@crm.local',
            'password' => 'password123',
            'role' => 'manager',
            'role_id' => $managerRole->id,
            'is_super_admin' => false,
        ]);

        $this->sales = User::create([
            'name' => 'مبيعات تجريبي',
            'email' => 'sales-test@crm.local',
            'password' => 'password123',
            'role' => 'sales',
            'role_id' => $salesRole->id,
            'is_super_admin' => false,
        ]);
    }

    public function test_super_admin_can_access_admin_screens(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/dashboard')->assertOk();

        $this->actingAs($this->superAdmin)
            ->get('/users')->assertOk();

        $this->actingAs($this->superAdmin)
            ->get('/roles')->assertOk();

        $this->actingAs($this->superAdmin)
            ->get('/campaigns')->assertOk();

        $this->actingAs($this->superAdmin)
            ->get('/profile')->assertOk()
            ->assertSee('البروفايل');
    }

    public function test_manager_can_access_management_but_not_blocked_from_customers(): void
    {
        $this->actingAs($this->manager)
            ->get('/dashboard')->assertOk();

        $this->actingAs($this->manager)
            ->get('/users')->assertOk();

        $this->actingAs($this->manager)
            ->get('/roles')->assertOk();

        $this->actingAs($this->manager)
            ->get('/customers')->assertOk();
    }

    public function test_sales_cannot_access_user_and_role_management(): void
    {
        $this->actingAs($this->sales)
            ->get('/dashboard')->assertOk();

        $this->actingAs($this->sales)
            ->get('/customers')->assertOk();

        $this->actingAs($this->sales)
            ->get('/users')->assertForbidden();

        $this->actingAs($this->sales)
            ->get('/roles')->assertForbidden();

        $this->actingAs($this->sales)
            ->get('/campaigns')->assertForbidden();
    }

    public function test_sales_can_access_profile(): void
    {
        $this->actingAs($this->sales)
            ->get('/profile')->assertOk()
            ->assertSee('البروفايل');

        $this->actingAs($this->sales)
            ->get('/password')
            ->assertRedirect('/profile');
    }

    public function test_super_admin_flag_is_detected_correctly(): void
    {
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertTrue($this->superAdmin->canDo('users.manage'));
        $this->assertFalse($this->manager->isSuperAdmin());
        $this->assertTrue($this->manager->canDo('users.manage'));
        $this->assertFalse($this->sales->canDo('users.manage'));
    }

    public function test_manager_can_create_user_with_super_admin_checkbox(): void
    {
        $superAdminRole = Role::where('slug', 'super_admin')->firstOrFail();
        $company = Company::firstOrFail();

        $response = $this->actingAs($this->manager)->post('/users', [
            'name' => 'مستخدم سوبر',
            'email' => 'new-super@crm.local',
            'password' => 'password123',
            'role_id' => $superAdminRole->id,
            'is_super_admin' => '1',
            'company_ids' => [$company->id],
        ]);

        $response->assertRedirect('/users');

        $user = User::where('email', 'new-super@crm.local')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_super_admin);
        $this->assertSame($superAdminRole->id, $user->role_id);
    }
}
