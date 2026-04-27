<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Projects Terminal</title>
</head>
<body>

    <h1>Employee Common Projects Finder</h1>

    <form method="POST" action="{{ route('employees.process') }}" enctype="multipart/form-data">
        @csrf

        <label for="file">Select CSV file:</label><br>
        <input type="file" id="file" name="file">

        @error('file')
            <p>{{ $message }}</p>
        @enderror

        <br><br>
        <button type="submit">Process</button>
    </form>

    @if(!empty($parseErrors))
        <h2>Parse Errors</h2>
        <ul>
            @foreach($parseErrors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if(isset($results))
        @if(count($results) > 0)
            <h2>Results (sorted by total days worked together)</h2>
            <p>Longest pair:
                {{ reset($results)['emp1'] }},
                {{ reset($results)['emp2'] }},
                {{ reset($results)['total_days'] }} days
            </p>

            <table border="1">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Employee #1</th>
                        <th>Employee #2</th>
                        <th>Days</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $pair)
                        @foreach($pair['projects'] as $project)
                            <tr>
                                <td>{{ $project['project_id'] }}</td>
                                <td>{{ $pair['emp1'] }}</td>
                                <td>{{ $pair['emp2'] }}</td>
                                <td>{{ $project['days'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No common projects found.</p>
        @endif
    @endif

</body>
</html>
