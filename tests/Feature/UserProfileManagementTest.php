<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Manager']);

        $this->user = User::factory()->create([
            'name'     => 'Abebe Kebede',
            'email'    => 'abebe@example.com',
            'phone'    => '+251911223344',
            'password' => Hash::make('Secret123!'),
        ]);

        $this->user->assignRole('Manager');
    }

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('profile.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile.index'));

        $response->assertStatus(200);
        $response->assertSee('Profile Information');
        $response->assertSee('Security Settings');
        $response->assertSee('Abebe Kebede');
        $response->assertSee('abebe@example.com');
        $response->assertSee('Change Password');
    }

    public function test_user_can_view_change_password_tab(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile.index', ['tab' => 'password']));

        $response->assertStatus(200);
        $response->assertSee('Current Password');
        $response->assertSee('New Password');
        $response->assertSee('Confirm New Password');
    }

    public function test_user_can_update_profile_information(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name'  => 'Abebe B. Kebede',
            'email' => 'abebe.updated@example.com',
            'phone' => '+251922334455',
        ]);

        $response->assertRedirect(route('profile.index', ['tab' => 'profile']));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals('Abebe B. Kebede', $this->user->name);
        $this->assertEquals('abebe.updated@example.com', $this->user->email);
        $this->assertEquals('+251922334455', $this->user->phone);
    }

    public function test_user_cannot_update_profile_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name'  => 'Abebe Kebede',
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_change_password_successfully(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password.update'), [
            'current_password'      => 'Secret123!',
            'password'              => 'NewSecurePass987!',
            'password_confirmation' => 'NewSecurePass987!',
        ]);

        $response->assertRedirect(route('profile.index', ['tab' => 'password']));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check('NewSecurePass987!', $this->user->password));
    }

    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password.update'), [
            'current_password'      => 'WrongPassword',
            'password'              => 'NewSecurePass987!',
            'password_confirmation' => 'NewSecurePass987!',
        ]);

        $response->assertRedirect(route('profile.index', ['tab' => 'password']));
        $response->assertSessionHasErrors('current_password');

        $this->user->refresh();
        $this->assertTrue(Hash::check('Secret123!', $this->user->password));
    }

    public function test_user_cannot_change_password_with_unconfirmed_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password.update'), [
            'current_password'      => 'Secret123!',
            'password'              => 'NewSecurePass987!',
            'password_confirmation' => 'MismatchedPass!',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
