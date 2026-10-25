<!DOCTYPE html>
<html>
<head>
    <title>Student Details</title>
</head>
<body>
    <h1>Student Details</h1>

    <p><strong>ID:</strong> {{ $student->id }}</p>
    <p><strong>Name:</strong> {{ $student->name }}</p>
    <p><strong>Email:</strong> {{ $student->email }}</p>
    <p><strong>Age:</strong> {{ $student->age }}</p>
    <p><strong>Course:</strong> {{ $student->course }}</p>
    <p><strong>Created At:</strong> {{ $student->created_at }}</p>
    <p><strong>Updated At:</strong> {{ $student->updated_at }}</p>

    <p>
        <a href="{{ route('students.edit', $student->id) }}">Edit</a>
        <a href="{{ route('students.index') }}">Back to List</a>
    </p>
</body>
</html>
