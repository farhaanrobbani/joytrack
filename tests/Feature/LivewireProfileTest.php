<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated_via_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile-update-information')
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->call('save')
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('profile-updated', app('session')->get('status'));
    }

    public function test_email_verification_status_is_unchanged_when_email_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile-update-information')
            ->call('save')
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_profile_update_requires_unique_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile-update-information')
            ->set('email', $other->email)
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_wrong_current_password_is_rejected_when_updating_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile-update-password')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-secret-password')
            ->set('password_confirmation', 'new-secret-password')
            ->call('save')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_password_can_be_updated_via_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile-update-password')
            ->set('current_password', 'password')
            ->set('password', 'new-secret-password')
            ->set('password_confirmation', 'new-secret-password')
            ->call('save')
            ->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('new-secret-password', $user->refresh()->password));
        $this->assertSame('password-updated', app('session')->get('status'));
    }

    public function test_delete_confirmation_modal_toggles(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $message = __('Are you sure you want to delete your account?');

        Livewire::test('profile-delete-user')
            ->assertDontSee($message)
            ->call('confirmUserDeletion')
            ->assertSee($message)
            ->call('cancelUserDeletion')
            ->assertDontSee($message);

        $this->assertNotNull($user->fresh());
    }

    public function test_account_can_be_deleted_via_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile-delete-user')
            ->call('confirmUserDeletion')
            ->set('password', 'password')
            ->call('deleteAccount')
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
        $this->assertGuest();
    }

    public function test_wrong_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile-delete-user')
            ->call('confirmUserDeletion')
            ->set('password', 'wrong-password')
            ->call('deleteAccount')
            ->assertHasErrors(['password']);

        $this->assertNotNull($user->fresh());
        $this->assertAuthenticatedAs($user);
    }
}
