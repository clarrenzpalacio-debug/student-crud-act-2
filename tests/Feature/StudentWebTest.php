<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_web_students_index(): void
    {
        $student = Student::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.web@example.com',
            'course' => 'BSIT',
            'year_level' => 3,
        ]);

        $response = $this->get('/students');
        $response->assertStatus(200);
        $response->assertSee('John');
        $response->assertSee('john.web@example.com');
    }

    public function test_can_view_create_student_form(): void
    {
        $response = $this->get('/students/create');
        $response->assertStatus(200);
        $response->assertSee('Add New Student');
    }

    public function test_can_store_student_via_web(): void
    {
        $response = $this->withSession(['_token' => 'secret'])
            ->post('/students', [
                '_token' => 'secret',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane.web@example.com',
                'course' => 'BSCS',
                'year_level' => 2,
            ]);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', ['email' => 'jane.web@example.com']);
    }

    public function test_can_view_student_details(): void
    {
        $student = Student::create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice.web@example.com',
            'course' => 'BSECE',
            'year_level' => 4,
        ]);

        $response = $this->get("/students/{$student->id}");
        $response->assertStatus(200);
        $response->assertSee('alice.web@example.com');
    }

    public function test_can_view_edit_student_form(): void
    {
        $student = Student::create([
            'first_name' => 'Bob',
            'last_name' => 'Builder',
            'email' => 'bob.web@example.com',
            'course' => 'BSCE',
            'year_level' => 1,
        ]);

        $response = $this->get("/students/{$student->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('bob.web@example.com');
    }

    public function test_can_update_student_via_web(): void
    {
        $student = Student::create([
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'email' => 'charlie.web@example.com',
            'course' => 'BSEE',
            'year_level' => 3,
        ]);

        $response = $this->withSession(['_token' => 'secret'])
            ->put("/students/{$student->id}", [
                '_token' => 'secret',
                'first_name' => 'Charles',
                'last_name' => 'Brown',
                'email' => 'charles.web@example.com',
                'course' => 'BSEE',
                'year_level' => 4,
            ]);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', ['email' => 'charles.web@example.com', 'year_level' => 4]);
    }

    public function test_can_delete_student_via_web(): void
    {
        $student = Student::create([
            'first_name' => 'David',
            'last_name' => 'Jones',
            'email' => 'david.web@example.com',
            'course' => 'BSME',
            'year_level' => 5,
        ]);

        $response = $this->withSession(['_token' => 'secret'])
            ->delete("/students/{$student->id}", [
                '_token' => 'secret',
            ]);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}
