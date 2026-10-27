<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(): JsonResponse
    {
        $students = Student::latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $students,
        ], 200);
    }

    /**
     * Store a newly created student in storage (Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Student_id' => 'required|string|unique:students,Student_id',
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:students,email',
            'course'     => 'required|string|max:255',
            'year_level' => 'required|integer|min:1|max:5',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Student record created successfully.',
            'data'    => $student,
        ], 201);
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $student,
        ], 200);
    }

    /**
     * Update the specified student in storage (Admin only).
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'Student_id' => 'sometimes|required|string|unique:students,Student_id,' . $student->id,
            'first_name' => 'sometimes|required|string|max:255',
            'last_name'  => 'sometimes|required|string|max:255',
            'email'      => 'sometimes|required|email|max:255|unique:students,email,' . $student->id,
            'course'     => 'sometimes|required|string|max:255',
            'year_level' => 'sometimes|required|integer|min:1|max:5',
        ]);

        $student->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Student record updated successfully.',
            'data'    => $student,
        ], 200);
    }

    /**
     * Remove the specified student from storage (Admin only).
     */
    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Student record deleted successfully.',
        ], 200);
    }
}
