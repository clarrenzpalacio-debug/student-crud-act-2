<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the exact admin and regular user accounts
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * 1. Test login with valid credentials returns a Sanctum token and user payload.
     */
    public function test_login_with_valid_credentials_returns_token_and_user(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'token',
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertEquals('admin', $response->json('user.role'));
    }

    /**
     * 2. Test login with invalid credentials is rejected.
     */
    public function test_login_with_invalid_credentials_is_rejected(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * 3. Test protected API requests work with a valid Bearer token.
     */
    public function test_protected_api_request_works_with_valid_bearer_token(): void
    {
        $user = User::where('email', 'user@example.com')->first();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/students');

        $response->assertStatus(200);
    }

    /**
     * 4. Test protected API requests reject invalid tokens.
     */
    public function test_protected_api_request_rejects_invalid_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-dummy-token-12345')
            ->getJson('/api/students');

        $response->assertStatus(401);
    }

    /**
     * 5 & 6. Test logout revokes current token and revoked token cannot access protected endpoints.
     */
    public function test_logout_revokes_token_and_prevents_subsequent_access(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $token = $admin->createToken('logout_test_token')->plainTextToken;

        // Verify token works
        $checkResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/students');
        $checkResponse->assertStatus(200);

        // Perform logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');
        $logoutResponse->assertStatus(200)
            ->assertJsonPath('message', 'Logged out successfully.');

        // Refresh database state / auth guard cache
        $this->app['auth']->forgetGuards();

        // Verify the exact same token is now rejected with 401
        $subsequentResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/students');
        $subsequentResponse->assertStatus(401);
    }

    /**
     * 7. Test regular users cannot perform admin-only operations (403 Forbidden).
     */
    public function test_regular_user_cannot_perform_admin_operations(): void
    {
        $regularUser = User::where('email', 'user@example.com')->first();
        $token = $regularUser->createToken('user_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/students', [
                'Student_id' => 'FORBIDDEN-2026',
                'first_name' => 'Sample',
                'last_name' => 'User',
                'email' => 'sample.forbidden@example.com',
                'course' => 'BSIT',
                'year_level' => 1,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Forbidden. You do not have the required permissions.');
    }

    /**
     * 8. Test administrator retains full CRUD permissions.
     */
    public function test_administrator_retains_full_crud_permissions(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $token = $admin->createToken('admin_crud_token')->plainTextToken;
        $uniqueId = 'ADM-' . time();

        // Admin Create
        $createResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/students', [
                'Student_id' => $uniqueId,
                'first_name' => 'AuthTest',
                'last_name' => 'Student',
                'email' => "{$uniqueId}@example.com",
                'course' => 'BSCS',
                'year_level' => 4,
            ]);
        $createResponse->assertStatus(201);
        $studentId = $createResponse->json('data.id');

        // Admin Update
        $updateResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/students/{$studentId}", [
                'Student_id' => $uniqueId,
                'first_name' => 'AuthTestUpdated',
                'last_name' => 'Student',
                'email' => "{$uniqueId}@example.com",
                'course' => 'BSCS',
                'year_level' => 5,
            ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.first_name', 'AuthTestUpdated');

        // Admin Delete
        $deleteResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/students/{$studentId}");
        $deleteResponse->assertStatus(200);
    }
}
