<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleLoginDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Manager', 'Seller', 'Production Officer', 'Auditor', 'Client'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_seller_login_redirects_to_dashboard(): void
    {
        $seller = User::factory()->create([
            'email'    => 'seller_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        $seller->assignRole('Seller');

        $response = $this->post(route('login.submit'), [
            'email'    => $seller->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_manager_login_redirects_to_dashboard(): void
    {
        $manager = User::factory()->create([
            'email'    => 'manager_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        $manager->assignRole('Manager');

        $response = $this->post(route('login.submit'), [
            'email'    => $manager->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_production_officer_login_redirects_to_manufacturing_dashboard(): void
    {
        $productionUser = User::factory()->create([
            'email'    => 'mfg_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        $productionUser->assignRole('Production Officer');

        $response = $this->post(route('login.submit'), [
            'email'    => $productionUser->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('manufacturing.dashboard'));
    }

    public function test_csrf_token_mismatch_redirects_to_login_gracefully_without_raw_419(): void
    {
        // Simulate a request without valid CSRF or with TokenMismatchException
        $exception = new \Illuminate\Session\TokenMismatchException('CSRF token mismatch.');
        
        $request = \Illuminate\Http\Request::create('/login', 'POST', [
            'email' => 'anyone@example.com',
            'password' => 'secret',
            '_token' => 'invalid_or_expired_token',
        ]);

        $response = app()->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->render($request, $exception);

        // It should redirect to /login with session status, not return 419 error page
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('login'), $response->headers->get('Location'));
    }
}
