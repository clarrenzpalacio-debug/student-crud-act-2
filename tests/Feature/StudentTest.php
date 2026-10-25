<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_students_list(): void
    {
        Student::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.list@example.com',
            'course' => 'BSIT',
            'year_level' => 3,
        ]);

        $response = $this->getJson('/api/students');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['email' => 'john.list@example.com']);
    }

    public function test_can_create_student(): void
    {
        $response = $this->postJson('/api/students', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.create@example.com',
            'course' => 'BSCS',
            'year_level' => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['email' => 'john.create@example.com']);
        $this->assertDatabaseHas('students', ['email' => 'john.create@example.com']);
    }

    public function test_can_show_student(): void
    {
        $student = Student::create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'course' => 'BSECE',
            'year_level' => 4,
        ]);

        $response = $this->getJson("/api/students/{$student->id}");
        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => 'alice@example.com']);
    }

    public function test_can_update_student(): void
    {
        $student = Student::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'course' => 'BSIS',
            'year_level' => 1,
        ]);

        $response = $this->putJson("/api/students/{$student->id}", [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'course' => 'BSIS',
            'year_level' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => 'jane.smith@example.com', 'year_level' => 2]);
        $this->assertDatabaseHas('students', ['email' => 'jane.smith@example.com', 'year_level' => 2]);
    }

    public function test_can_delete_student(): void
    {
        $student = Student::create([
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'bob@example.com',
            'course' => 'BSME',
            'year_level' => 5,
        ]);

        $response = $this->deleteJson("/api/students/{$student->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Student record deleted successfully.']);
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}
