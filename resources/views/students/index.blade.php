<!DOCTYPE html>
<html>
<head>
    <title>Students List</title>
</head>
<body>
    <h1>Students CRUD</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <p>
        <a href="{{ route('students.create') }}">Add New Student</a>
    </p>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Year Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    <td>{{ $student->id }}</td>
                    <td>{{ $student->first_name }}</td>
                    <td>{{ $student->last_name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->course }}</td>
                    <td>{{ $student->year_level }}</td>
                    <td>
                        <a href="{{ route('students.show', $student->id) }}">View</a>
                        <a href="{{ route('students.edit', $student->id) }}">Edit</a>
                        <form action="{{ route('students.destroy', $student->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
