<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
</head>
<body>
    <h1>Edit Student</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('students.update', $student->id) }}" method="POST">
        @csrf
        @method('PUT')
        <p>
            <label>Name:</label><br>
            <input type="text" name="name" value="{{ old('name', $student->name) }}">
        </p>
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="{{ old('email', $student->email) }}">
        </p>
        <p>
            <label>Age:</label><br>
            <input type="number" name="age" value="{{ old('age', $student->age) }}">
        </p>
        <p>
            <label>Course:</label><br>
            <input type="text" name="course" value="{{ old('course', $student->course) }}">
        </p>
        <p>
            <button type="submit">Update</button>
            <a href="{{ route('students.index') }}">Cancel</a>
        </p>
    </form>
</body>
</html>
