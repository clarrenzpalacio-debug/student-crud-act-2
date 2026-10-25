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
        $response = $this->get('/students');
        $response->assertStatus(200);
        $response->assertSee('Students CRUD');
    }

    public function test_can_create_student(): void
    {
        $response = $this->post('/students', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 20,
            'course' => 'Computer Science',
        ]);

        $response->assertRedirect('/students');
        $this->assertDatabaseHas('students', ['email' => 'john@example.com']);
    }

    public function test_can_update_student(): void
    {
        $student = Student::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'age' => 22,
            'course' => 'Mathematics',
        ]);

        $response = $this->put("/students/{$student->id}", [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'age' => 23,
            'course' => 'Applied Mathematics',
        ]);

        $response->assertRedirect('/students');
        $this->assertDatabaseHas('students', ['email' => 'jane.smith@example.com']);
    }

    public function test_can_delete_student(): void
    {
        $student = Student::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'age' => 21,
            'course' => 'Physics',
        ]);

        $response = $this->delete("/students/{$student->id}");

        $response->assertRedirect('/students');
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}
