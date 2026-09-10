<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use App\Models\FuelRecord;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_attachment_to_transaction(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
        $tx = Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $category->id]);

        $this->actingAs($user);
        $file = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->post(route('attachments.store'), [
            'attachable_type' => 'transaction',
            'attachable_id' => $tx->id,
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attachments', ['user_id' => $user->id, 'attachable_id' => $tx->id, 'attachable_type' => Transaction::class]);
        Storage::disk('public')->assertExists('attachments/transaction/' . $tx->id . '/' . $file->hashName());
    }

    public function test_can_upload_to_fuel_record(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $fuel = FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id]);
        $this->actingAs($user);
        $file = UploadedFile::fake()->create('nota.pdf', 100, 'application/pdf');

        $this->post(route('attachments.store'), [
            'attachable_type' => 'fuel_record',
            'attachable_id' => $fuel->id,
            'file' => $file,
        ])->assertRedirect();

        $this->assertDatabaseHas('attachments', ['attachable_type' => FuelRecord::class, 'attachable_id' => $fuel->id]);
    }

    public function test_file_validation(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
        $tx = Transaction::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'category_id' => $category->id]);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');
        $response = $this->post(route('attachments.store'), [
            'attachable_type' => 'transaction',
            'attachable_id' => $tx->id,
            'file' => $file,
        ]);
        $response->assertSessionHasErrors('file');
    }

    public function test_can_preview_and_delete(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $fuel = FuelRecord::factory()->create(['user_id' => $user->id, 'vehicle_id' => $vehicle->id]);
        $this->actingAs($user);
        $file = UploadedFile::fake()->image('photo.jpg');
        $this->post(route('attachments.store'), [
            'attachable_type' => 'fuel_record',
            'attachable_id' => $fuel->id,
            'file' => $file,
        ]);

        $attachment = \App\Models\Attachment::first();
        $this->get(route('attachments.show', $attachment))->assertStatus(200);

        $this->delete(route('attachments.destroy', $attachment))->assertRedirect();
        $this->assertDatabaseCount('attachments', 0);
        Storage::disk('public')->assertMissing($attachment->file_path);
    }

    public function test_ownership_isolation(): void
    {
        Storage::fake('public');
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $u1->id]);
        $fuel = FuelRecord::factory()->create(['user_id' => $u1->id, 'vehicle_id' => $vehicle->id]);
        $this->actingAs($u1);
        $file = UploadedFile::fake()->image('a.jpg');
        $this->post(route('attachments.store'), [
            'attachable_type' => 'fuel_record',
            'attachable_id' => $fuel->id,
            'file' => $file,
        ]);
        $attachment = \App\Models\Attachment::first();

        $this->actingAs($u2);
        $this->get(route('attachments.show', $attachment))->assertStatus(403);
        $this->delete(route('attachments.destroy', $attachment))->assertStatus(403);
    }

    public function test_cannot_upload_to_others_record(): void
    {
        Storage::fake('public');
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $u1->id]);
        $fuel = FuelRecord::factory()->create(['user_id' => $u1->id, 'vehicle_id' => $vehicle->id]);
        $this->actingAs($u2);
        $file = UploadedFile::fake()->image('b.jpg');
        $response = $this->post(route('attachments.store'), [
            'attachable_type' => 'fuel_record',
            'attachable_id' => $fuel->id,
            'file' => $file,
        ]);
        $response->assertStatus(403);
    }
}
