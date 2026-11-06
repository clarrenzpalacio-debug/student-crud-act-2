<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VueCrudApiTest extends TestCase
{
    /**
     * Test admin can perform full CRUD lifecycle.
     */
    public function test_admin_can_perform_full_crud(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test_admin_token')->plainTextToken;

        $headers = [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];

        // 1. CREATE
        $uniqueId = 'TEST-' . time();
        $payload = [
            'Student_id' => $uniqueId,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => "maria.{$uniqueId}@example.com",
            'course' => 'BSCS',
            'year_level' => 2,
        ];

        $createResponse = $this->withHeaders($headers)->postJson('/api/students', $payload);
        $createResponse->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.Student_id', $uniqueId);

        $studentId = $createResponse->json('data.id');

        // 2. READ (LIST & SHOW)
        $listResponse = $this->withHeaders($headers)->getJson('/api/students');
        $listResponse->assertStatus(200);

        $showResponse = $this->withHeaders($headers)->getJson("/api/students/{$studentId}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.email', "maria.{$uniqueId}@example.com");

        // 3. UPDATE
        $updatePayload = [
            'Student_id' => $uniqueId,
            'first_name' => 'Maria Clara',
            'last_name' => 'Santos-Reyes',
            'email' => "maria.clara.{$uniqueId}@example.com",
            'course' => 'BSCS',
            'year_level' => 3,
        ];

        $updateResponse = $this->withHeaders($headers)->putJson("/api/students/{$studentId}", $updatePayload);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.first_name', 'Maria Clara')
            ->assertJsonPath('data.year_level', 3);

        // 4. DELETE
        $deleteResponse = $this->withHeaders($headers)->deleteJson("/api/students/{$studentId}");
        $deleteResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Verify gone
        $verifyResponse = $this->withHeaders($headers)->getJson("/api/students/{$studentId}");
        $verifyResponse->assertStatus(404);
    }

    /**
     * Test regular user is forbidden from create, update, and delete.
     */
    public function test_regular_user_cannot_mutate_records(): void
    {
        $user = User::where('role', 'user')->first() ?? User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test_user_token')->plainTextToken;

        $headers = [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];

        // Read works
        $readResponse = $this->withHeaders($headers)->getJson('/api/students');
        $readResponse->assertStatus(200);

        // Create is forbidden
        $createResponse = $this->withHeaders($headers)->postJson('/api/students', [
            'Student_id' => 'FORBIDDEN-01',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'forbidden@example.com',
            'course' => 'BSIT',
            'year_level' => 1,
        ]);
        $createResponse->assertStatus(403);
    }
}

