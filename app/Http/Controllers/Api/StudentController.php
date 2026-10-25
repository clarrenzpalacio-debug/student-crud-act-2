<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        return response()->json(Student::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:students,email',
            'course' => 'required|string',
            'year_level' => 'required|integer|min:1|max:5',
        ]);

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    public function show(Student $student)
    {
        return response()->json($student);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:students,email,' . $student->id,
            'course' => 'sometimes|required|string',
            'year_level' => 'sometimes|required|integer|min:1|max:5',
        ]);

        $student->update($validated);

        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return response()->json([
            'message' => 'Student record deleted successfully.'
        ], 200);
    }
}
