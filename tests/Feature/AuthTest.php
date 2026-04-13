<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // RefreshDatabase akan reset database setiap kali test dijalankan
    // sehingga data antar test tidak saling mengganggu
    use RefreshDatabase;

    // =========================================================
    // REGISTER TESTS
    // =========================================================

    /** @test */
    public function user_dapat_register_dengan_data_valid(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Harus return 201 Created
        $response->assertStatus(201);

        // Harus ada field status, user di response
        $response->assertJsonStructure([
            'status',
            'user' => ['id', 'name', 'email', 'joined_at'],
        ]);

        // Status harus 'success'
        $response->assertJson(['status' => 'success']);

        // Pastikan data tersimpan di database
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    /** @test */
    public function register_gagal_jika_email_sudah_terdaftar(): void
    {
        // Buat user duluan dengan factory
        User::factory()->create(['email' => 'budi@example.com']);

        $response = $this->postJson('/api/register', [
            'name'                  => 'Budi Lain',
            'email'                 => 'budi@example.com', // email sama
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Harus ditolak dengan 422 Unprocessable Entity
        $response->assertStatus(422);

        // Harus ada error di field email
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function register_gagal_jika_password_tidak_cocok(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'passwordSALAH', // tidak cocok
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    /** @test */
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

    /** @test */
    public function register_gagal_jika_field_kosong(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function password_tidak_muncul_di_response_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name'                  => 'Budi Santoso',
            'email'                 => 'budi@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Pastikan password TIDAK ada di response (penting untuk keamanan!)
        $response->assertJsonMissing(['password']);
    }

    // =========================================================
    // LOGIN TESTS
    // =========================================================

    /** @test */
    public function user_dapat_login_dengan_kredensial_benar(): void
    {
        // Buat user dulu
        User::factory()->create([
            'email'    => 'budi@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'budi@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        // Harus ada token di response
        $response->assertJsonStructure([
            'status',
            'token',
            'user' => ['id', 'name', 'email', 'joined_at'],
        ]);

        $response->assertJson(['status' => 'success']);
    }

    /** @test */
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

        // Harus return 422
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_gagal_jika_email_tidak_terdaftar(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'tidakada@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function login_gagal_jika_field_kosong(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    // =========================================================
    // LOGOUT TESTS
    // =========================================================

    /** @test */
    public function user_dapat_logout_dengan_token_valid(): void
    {
        $user = User::factory()->create();

        // Buat token untuk user
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'message' => 'Berhasil logout, token telah dihapus.',
        ]);

        // Pastikan token sudah dihapus dari database
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /** @test */
    public function logout_gagal_tanpa_token(): void
    {
        $response = $this->postJson('/api/logout');

        // Harus return 401 Unauthorized
        $response->assertStatus(401);
    }

    /** @test */
    public function logout_tidak_bisa_pakai_token_yang_sudah_dipakai(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Logout pertama — berhasil
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout')
            ->assertStatus(200);

        // Logout kedua dengan token yang sama — harus gagal
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout')
            ->assertStatus(401);
    }
}
