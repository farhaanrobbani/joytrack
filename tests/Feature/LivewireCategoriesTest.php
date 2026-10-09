<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_category(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('categories-create')
            ->set('name', 'Transportasi')
            ->set('type', 'expense')
            ->set('icon', 'bus')
            ->call('save')
            ->assertRedirect(route('categories.index'));

        $this->assertSame(__('Kategori berhasil dibuat.'), app('session')->get('status'));
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Transportasi',
            'type' => 'expense',
            'icon' => 'bus',
            'is_active' => true,
        ]);
    }

    public function test_create_requires_name(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('categories-create')
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_create_rejects_invalid_type(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('categories-create')
            ->set('name', 'Transfer')
            ->set('type', 'transfer')
            ->call('save')
            ->assertHasErrors(['type']);
    }

    public function test_edit_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Nama Lama', 'type' => 'expense']);
        $this->actingAs($user);

        Livewire::test('categories-edit', ['category' => $category])
            ->set('name', 'Nama Baru')
            ->set('type', 'income')
            ->set('is_active', false)
            ->call('save')
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Nama Baru',
            'type' => 'income',
            'is_active' => false,
        ]);
    }

    public function test_edit_is_forbidden_for_other_users(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();

        $this->expectException(AuthorizationException::class);
        Livewire::test('categories-edit', ['category' => $category]);
    }
}
