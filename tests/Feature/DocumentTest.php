<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ExpiryReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_document_index(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('documents.index'))->assertStatus(200);
    }

    public function test_guest_cannot_access_documents(): void
    {
        $this->get(route('documents.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_create_document(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('documents.store'), [
            'name' => 'STNK Mobil',
            'document_type' => 'stnk',
            'expiry_date' => now()->addMonths(3)->format('Y-m-d'),
            'reminder_days' => 14,
            'notes' => 'Perpanjang di Samsat',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('documents.index'));
        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'name' => 'STNK Mobil',
            'document_type' => 'stnk',
            'reminder_days' => 14,
        ]);
    }

    public function test_user_can_update_document(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $document = Document::factory()->create(['user_id' => $user->id]);

        $response = $this->patch(route('documents.update', $document), [
            'name' => 'STNK Updated',
            'document_type' => 'stnk',
            'expiry_date' => $document->expiry_date->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $response->assertRedirect(route('documents.index'));
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'name' => 'STNK Updated']);
    }

    public function test_user_can_delete_document(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $document = Document::factory()->create(['user_id' => $user->id]);

        $this->delete(route('documents.destroy', $document))->assertRedirect(route('documents.index'));
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_user_cannot_edit_others_document(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user1->id]);

        $this->actingAs($user2);

        $this->get(route('documents.edit', $document))->assertStatus(403);
        $this->delete(route('documents.destroy', $document))->assertStatus(403);
    }

    public function test_document_ownership_isolation(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Document::factory()->create(['user_id' => $user1->id]);
        Document::factory()->create(['user_id' => $user1->id]);
        Document::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);
        $documents = Livewire::test('documents-index')->viewData('documents');

        $this->assertSame(2, $documents->count());
    }

    public function test_store_rejects_invalid_document_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('documents.store'), [
            'name' => 'Dokumen',
            'document_type' => 'kartu-keluarga',
            'expiry_date' => now()->addMonth()->format('Y-m-d'),
            'reminder_days' => 7,
        ])->assertSessionHasErrors('document_type');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_store_rejects_reminder_days_out_of_range(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('documents.store'), [
            'name' => 'Dokumen',
            'document_type' => 'stnk',
            'expiry_date' => now()->addMonth()->format('Y-m-d'),
            'reminder_days' => 0,
        ])->assertSessionHasErrors('reminder_days');
    }

    public function test_store_rejects_vehicle_owned_by_other_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);

        $this->post(route('documents.store'), [
            'name' => 'STNK',
            'document_type' => 'stnk',
            'vehicle_id' => $vehicle->id,
            'expiry_date' => now()->addMonth()->format('Y-m-d'),
            'reminder_days' => 7,
        ])->assertSessionHasErrors('vehicle_id');
    }

    public function test_document_reminder_overdue(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => now()->subDays(5)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $reminders = app(ExpiryReminderService::class)->getDocumentReminders($user->id);

        $this->assertCount(1, $reminders);
        $this->assertEquals('overdue', $reminders->first()['status']);
        $this->assertEquals('document', $reminders->first()['source']);
    }

    public function test_document_reminder_due_soon_respects_custom_threshold(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => now()->addDays(5)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);
        Document::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => now()->addDays(5)->format('Y-m-d'),
            'reminder_days' => 3,
        ]);

        $reminders = app(ExpiryReminderService::class)->getDocumentReminders($user->id);

        $this->assertCount(1, $reminders);
        $this->assertEquals('due_soon', $reminders->first()['status']);
    }

    public function test_document_reminder_ok_when_far(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => now()->addMonths(6)->format('Y-m-d'),
            'reminder_days' => 7,
        ]);

        $this->assertCount(0, app(ExpiryReminderService::class)->getDocumentReminders($user->id));
    }

    public function test_inactive_document_is_excluded_from_reminders(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => now()->subDay()->format('Y-m-d'),
            'is_active' => false,
        ]);

        $this->assertCount(0, app(ExpiryReminderService::class)->getDocumentReminders($user->id));
    }

    public function test_dashboard_shows_expiry_reminders(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'STNK Mobil',
            'expiry_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->actingAs($user);
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('STNK Mobil');
        $this->assertCount(1, Livewire::test('dashboard-index')->viewData('expiryReminders'));
    }

    public function test_document_index_shows_status_badge(): void
    {
        $user = User::factory()->create();
        Document::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => now()->subDays(3)->format('Y-m-d'),
        ]);

        $this->actingAs($user);
        $this->get(route('documents.index'))
            ->assertStatus(200)
            ->assertSee(__('Terlambat :days hari', ['days' => 3]));
    }
}
