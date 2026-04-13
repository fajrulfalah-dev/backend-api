<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // RefreshDatabase akan reset database setiap kali test dijalankan
    // sehingga data antar test tidak saling mengganggu
    use RefreshDatabase;

    // =========================================================
    // REGISTER TESTS
    // =========================================================

    #[Test]
    public function user_dapat_register_dengan_data_valid(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'user' => ['id', 'name', 'email', 'joined_at'],
        ]);
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    #[Test]
    public function register_gagal_jika_email_sudah_terdaftar(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $response = $this->postJson('/api/register', [
            'name'                  => 'Budi Lain',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function register_gagal_jika_password_tidak_cocok(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'passwordSALAH',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function register_gagal_jika_password_kurang_dari_8_karakter(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@example.com',
            'password'              => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function register_gagal_jika_field_kosong(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function password_tidak_muncul_di_response_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertJsonMissing(['password']);
    }

    // =========================================================
    // LOGIN TESTS
    // =========================================================

    #[Test]
    public function user_dapat_login_dengan_kredensial_benar(): void
    {
        User::factory()->create([
            'email'    => 'budi@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'budi@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'token',
            'user' => ['id', 'name', 'email', 'joined_at'],
        ]);
        $response->assertJson(['status' => 'success']);
    }

    #[Test]
    public function login_gagal_jika_password_salah(): void
    {
        User::factory()->create([
            'email'    => 'budi@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'budi@example.com',
            'password' => 'passwordSALAH',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function login_gagal_jika_email_tidak_terdaftar(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'tidakada@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function login_gagal_jika_field_kosong(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    // =========================================================
    // LOGOUT TESTS
    // =========================================================

    #[Test]
    public function user_dapat_logout_dengan_token_valid(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Berhasil logout, token telah dihapus.',
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    #[Test]
    public function logout_gagal_tanpa_token(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    #[Test]
    public function logout_tidak_bisa_pakai_token_yang_sudah_dipakai(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Logout pertama — berhasil
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout')
            ->assertStatus(200);

        // Logout kedua dengan token yang sama
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout')
            ->assertStatus(200);
    }
}