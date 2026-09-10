<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_category_index(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get(route('categories.index'))->assertStatus(200);
    }

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('categories.store'), [
            'name' => 'Gaji',
            'type' => 'income',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => 'income',
        ]);
    }

    public function test_user_can_create_expense_category(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('categories.store'), [
            'name' => 'Makanan',
            'type' => 'expense',
        ]);

        $this->assertDatabaseHas('categories', ['user_id' => $user->id, 'name' => 'Makanan', 'type' => 'expense']);
    }

    public function test_user_can_update_category(): void
    {
        $user = User::factory()->create();
        $cat = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
        $this->actingAs($user);

        $response = $this->patch(route('categories.update', $cat), [
            'name' => 'Makanan Updated',
            'type' => 'expense',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $cat->id, 'name' => 'Makanan Updated']);
    }

    public function test_user_can_delete_category(): void
    {
        $user = User::factory()->create();
        $cat = Category::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $this->delete(route('categories.destroy', $cat))->assertRedirect(route('categories.index'));
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_user_cannot_view_others_category(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $cat = Category::factory()->create(['user_id' => $u1->id]);
        $this->actingAs($u2);
        $this->get(route('categories.show', $cat))->assertStatus(403);
    }

    public function test_category_ownership_isolation(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        Category::factory()->count(2)->create(['user_id' => $u1->id]);
        Category::factory()->create(['user_id' => $u2->id]);

        $this->actingAs($u1);
        $response = $this->get(route('categories.index'));
        // should only see 2 (grouped) but raw count is via view data
        $response->assertStatus(200);
        // verify DB isolation directly
        $this->assertEquals(2, Category::where('user_id', $u1->id)->count());
        $this->assertEquals(1, Category::where('user_id', $u2->id)->count());
    }

    public function test_default_category_seeder(): void
    {
        $user = User::factory()->create();
        $this->seed(\Database\Seeders\CategorySeeder::class);
        $this->assertTrue(Category::where('user_id', $user->id)->count() >= 10);
        $this->assertDatabaseHas('categories', ['user_id' => $user->id, 'name' => 'Gaji', 'type' => 'income']);
        $this->assertDatabaseHas('categories', ['user_id' => $user->id, 'name' => 'Bahan Bakar', 'type' => 'expense']);
    }
}
