<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemSettingsLogoTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::findOrCreate('Super Admin');
        Role::findOrCreate('Admin');
        Role::findOrCreate('Seller');

        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@test.com',
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        $this->admin->assignRole('Admin');

        $this->seller = User::factory()->create([
            'email' => 'seller@test.com',
        ]);
        $this->seller->assignRole('Seller');
    }

    public function test_super_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('System & Branding Settings', false);
        $response->assertSee('Official Application Logo');
    }

    public function test_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('System & Branding Settings', false);
        $response->assertSee('Official Application Logo');
    }

    public function test_seller_cannot_access_settings_page(): void
    {
        $response = $this->actingAs($this->seller)->get(route('settings.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_settings_page(): void
    {
        $response = $this->get(route('settings.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_can_update_logo_and_settings(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('custom_logo.png', 300, 100);

        $response = $this->actingAs($this->superAdmin)->post(route('settings.update'), [
            'site_name'    => 'Albareck Oil & Manufacturing',
            'site_tagline' => 'Excellence in Quality',
            'logo'         => $file,
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('Albareck Oil & Manufacturing', Setting::get('site_name'));
        $this->assertEquals('Excellence in Quality', Setting::get('site_tagline'));

        $savedLogo = Setting::get('site_logo');
        $this->assertNotNull($savedLogo);
        Storage::disk('public')->assertExists($savedLogo);
    }

    public function test_super_admin_can_reset_logo_to_default(): void
    {
        Setting::set('site_logo', 'settings/custom_logo.png');

        $response = $this->actingAs($this->superAdmin)->post(route('settings.reset-logo'));

        $response->assertRedirect(route('settings.index'));
        $this->assertEquals('images/logo.jpg', Setting::get('site_logo'));
    }

    public function test_dashboard_renders_brand_logo_correctly(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('IMS Portal');
        $response->assertSee(Setting::getLogoUrl());
    }
}
