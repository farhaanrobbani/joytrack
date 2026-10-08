<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_uses_custom_error_page(): void
    {
        $response = $this->get('/halaman-tidak-ada');
        $response->assertStatus(404);
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('404');
    }

    public function test_403_uses_custom_error_page(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($intruder)->get(route('accounts.show', $account));
        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
    }

    public function test_error_page_links_back_to_home(): void
    {
        $response = $this->get('/halaman-tidak-ada');
        $response->assertStatus(404);
        $response->assertSee(route('home'), false);
    }
}
