<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_document(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        Livewire::test('documents-create')
            ->set('name', 'STNK Mobil')
            ->set('document_type', 'stnk')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('expiry_date', now()->addYear()->format('Y-m-d'))
            ->set('reminder_days', '14')
            ->set('notes', 'Perpanjang sebelum Lebaran')
            ->call('save')
            ->assertRedirect(route('documents.index'));

        $this->assertSame(__('Dokumen berhasil dibuat.'), app('session')->get('status'));
        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'name' => 'STNK Mobil',
            'document_type' => 'stnk',
            'reminder_days' => 14,
            'is_active' => true,
        ]);
    }

    public function test_create_requires_name_and_expiry_date(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('documents-create')
            ->call('save')
            ->assertHasErrors(['name', 'expiry_date']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_create_rejects_invalid_document_type(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('documents-create')
            ->set('name', 'Dokumen')
            ->set('document_type', 'bukan-jenis')
            ->set('expiry_date', now()->addYear()->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['document_type']);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_create_rejects_other_users_vehicle(): void
    {
        $owner = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $owner->id]);
        $this->actingAs(User::factory()->create());

        Livewire::test('documents-create')
            ->set('name', 'STNK Motor')
            ->set('document_type', 'stnk')
            ->set('vehicle_id', (string) $vehicle->id)
            ->set('expiry_date', now()->addYear()->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors(['vehicle_id' => __('Kendaraan tidak valid.')]);

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_edit_document(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'user_id' => $user->id,
            'name' => 'STNK Lama',
            'expiry_date' => now()->addMonths(2),
        ]);
        $this->actingAs($user);

        Livewire::test('documents-edit', ['document' => $document])
            ->set('name', 'STNK Baru')
            ->set('reminder_days', '30')
            ->call('save')
            ->assertRedirect(route('documents.index'));

        $this->assertSame(__('Dokumen berhasil diperbarui.'), app('session')->get('status'));
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'name' => 'STNK Baru',
            'reminder_days' => 30,
        ]);
    }

    public function test_edit_is_forbidden_for_other_users(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create(['user_id' => $user->id]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('documents-edit', ['document' => $document]);
    }
}
