<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\StorageLocation;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Product;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;

class UserActivityMonitoringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Manager', 'Seller', 'Production Officer', 'Auditor', 'Client'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_auth_events_are_recorded_accurately()
    {
        $user = User::factory()->create([
            'email'    => 'testuser@albareck.com',
            'password' => Hash::make('secret123'),
        ]);
        $user->assignRole('Manager');

        // 1. Simulate Login event
        event(new Login('web', $user, false));

        $loginLog = Activity::where('log_name', 'Auth')
            ->where('event', 'login')
            ->latest('id')
            ->first();

        $this->assertNotNull($loginLog);
        $this->assertEquals($user->id, $loginLog->causer_id);
        $this->assertStringContainsString('logged in successfully', $loginLog->description);
        $this->assertEquals('Manager', $loginLog->properties['role'] ?? null);

        // 2. Simulate Logout event
        event(new Logout('web', $user));

        $logoutLog = Activity::where('log_name', 'Auth')
            ->where('event', 'logout')
            ->latest('id')
            ->first();

        $this->assertNotNull($logoutLog);
        $this->assertEquals($user->id, $logoutLog->causer_id);
        $this->assertStringContainsString('logged out', $logoutLog->description);

        // 3. Simulate Failed Login event
        event(new Failed('web', null, ['email' => 'hacker@malicious.com', 'password' => 'wrongpass']));

        $failedLog = Activity::where('log_name', 'Auth')
            ->where('event', 'failed_login')
            ->latest('id')
            ->first();

        $this->assertNotNull($failedLog);
        $this->assertEquals('failed', $failedLog->properties['outcome'] ?? null);
        $this->assertEquals('hacker@malicious.com', $failedLog->properties['attempted_email'] ?? null);
        // Sensitive password must NOT be in the log!
        $this->assertArrayNotHasKey('password', $failedLog->properties ?? []);
    }

    public function test_user_changes_log_without_passwords_or_tokens()
    {
        $admin = User::factory()->create(['name' => 'Admin Operator']);
        $admin->assignRole('Super Admin');
        Auth::login($admin);

        $targetUser = User::create([
            'name'      => 'New Staff Member',
            'email'     => 'newstaff@albareck.com',
            'password'  => Hash::make('secretPassword999'),
            'is_active' => true,
        ]);

        $createLog = Activity::where('subject_type', User::class)
            ->where('subject_id', $targetUser->id)
            ->where('event', 'create')
            ->first();

        $this->assertNotNull($createLog);
        // Password must never be logged
        $this->assertArrayNotHasKey('password', $createLog->properties ?? []);

        // Update target user
        $targetUser->update(['name' => 'Renamed Staff Member', 'is_active' => false]);

        $updateLog = Activity::where('subject_type', User::class)
            ->where('subject_id', $targetUser->id)
            ->where('event', 'deactivated')
            ->latest('id')
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertEquals('deactivated', $updateLog->event);
    }

    public function test_sale_and_payment_activities_are_logged()
    {
        $branch = Branch::create(['name' => 'Bole Branch', 'code' => 'BOL-1', 'is_active' => true]);
        $location = StorageLocation::create(['branch_id' => $branch->id, 'name' => 'Bole Main', 'code' => 'BOL-MAIN', 'is_active' => true]);

        $seller = User::factory()->create(['name' => 'Bole Seller']);
        $seller->assignRole('Seller');
        Auth::login($seller);

        $sale = Sale::create([
            'reference'           => 'INV-ACT-999',
            'type'                => 'quick_sale',
            'user_id'             => $seller->id,
            'branch_id'           => $branch->id,
            'storage_location_id' => $location->id,
            'customer_name'       => 'Test Customer',
            'total'               => 1500.00,
            'paid_amount'         => 0.00,
            'payment_status'      => 'Unpaid',
            'status'              => 'Pending',
        ]);

        $saleLog = Activity::where('subject_type', Sale::class)
            ->where('subject_id', $sale->id)
            ->where('event', 'create')
            ->first();

        $this->assertNotNull($saleLog);
        $this->assertEquals('Sales', $saleLog->log_name);
        $this->assertEquals('INV-ACT-999', $saleLog->properties['reference'] ?? null);

        // Record Payment
        $payment = Payment::create([
            'sale_id'   => $sale->id,
            'amount'    => 1500.00,
            'method'    => 'Cash',
            'reference' => 'PAY-REC-001',
            'date'      => now(),
        ]);

        $paymentLog = Activity::where('log_name', 'Sales')
            ->where('event', 'payment')
            ->latest('id')
            ->first();

        $this->assertNotNull($paymentLog);
        $this->assertEquals(1500.00, $paymentLog->properties['amount'] ?? null);
    }

    public function test_activity_report_filtering_and_csv_export()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        // Create some activities
        \App\Services\ActivityLogger::log(
            module: 'Inventory',
            actionType: 'create',
            description: 'Created new product test item',
            causer: $admin,
            properties: ['role' => 'Super Admin', 'reference' => 'SKU-FILTER-TEST'],
            outcome: 'success'
        );

        \App\Services\ActivityLogger::log(
            module: 'Auth',
            actionType: 'failed_login',
            description: 'Failed login attempt test',
            causer: null,
            properties: ['attempted_email' => 'baduser@test.com'],
            outcome: 'failed'
        );

        // Filter by module 'Inventory'
        $response = $this->actingAs($admin)->get(route('reports.activity', ['module' => 'Inventory']));
        $response->assertStatus(200);
        $response->assertSee('Created new product test item');
        $response->assertDontSee('Failed login attempt test');

        // Filter by outcome 'failed'
        $responseFail = $this->actingAs($admin)->get(route('reports.activity', ['outcome' => 'failed']));
        $responseFail->assertStatus(200);
        $responseFail->assertSee('Failed login attempt test');
        $responseFail->assertDontSee('Created new product test item');

        // Export to CSV
        $exportResponse = $this->actingAs($admin)->get(route('reports.activity.export'));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename="user_activity_report_', $exportResponse->headers->get('content-disposition'));
    }
}
