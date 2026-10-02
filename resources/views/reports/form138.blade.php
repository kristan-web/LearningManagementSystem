<!DOCTYPE html>
<html>
<head>
    <title>Report Card - {{ $student->user->name }}</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>Report Card (Form 138)</h1>
    <p><strong>Student:</strong> {{ $student->user->name }}</p>
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Final Grade</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades as $grade)
            <tr>
                <td>{{ $grade->subject->subject_name }}</td>
                <td>{{ $grade->final_rating }}</td>
                <td>{{ $grade->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
