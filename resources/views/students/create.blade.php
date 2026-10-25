<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
</head>
<body>
    <h1>Add New Student</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('students.store') }}" method="POST">
        @csrf
        <p>
            <label>First Name:</label><br>
            <input type="text" name="first_name" value="{{ old('first_name') }}">
        </p>
        <p>
            <label>Last Name:</label><br>
            <input type="text" name="last_name" value="{{ old('last_name') }}">
        </p>
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="{{ old('email') }}">
        </p>
        <p>
            <label>Course:</label><br>
            <input type="text" name="course" value="{{ old('course') }}">
        </p>
        <p>
            <label>Year Level:</label><br>
            <input type="number" name="year_level" min="1" max="5" value="{{ old('year_level') }}">
        </p>
        <p>
            <button type="submit">Submit</button>
            <a href="{{ route('students.index') }}">Cancel</a>
        </p>
    </form>
</body>
</html>
