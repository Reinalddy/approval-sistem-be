<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials()
    {
        $role = Role::create(['name' => 'User']);
        $user = User::factory()->create([
            'email' => 'test@aqi.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@aqi.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role_id'],
                    'access_token',
                    'token_type'
                ]
            ]);
    }

    public function test_user_cannot_login_with_incorrect_credentials()
    {
        $role = Role::create(['name' => 'User']);
        $user = User::factory()->create([
            'email' => 'test@aqi.com',
            'password' => bcrypt('password123'),
            'role_id' => $role->id,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@aqi.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'code' => 401,
                'message' => 'Email atau password salah',
            ]);
    }

    public function test_user_can_logout()
    {
        $role = Role::create(['name' => 'User']);
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'message' => 'Logout berhasil',
            ]);
    }
}
