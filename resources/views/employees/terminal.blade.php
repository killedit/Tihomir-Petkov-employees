<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Projects Terminal</title>
</head>
<body style="background:#0c0c0c; margin:0; padding:20px; font-family:monospace;">
<div style="max-width:900px; margin:0 auto; background:#1a1a1a; border:1px solid #333; border-radius:4px; overflow:hidden;">
    <div style="background:#222; padding:8px 12px; border-bottom:1px solid #333; display:flex; gap:8px;">
        <span style="width:12px; height:12px; background:#ff5f56; border-radius:50%; display:inline-block;"></span>
        <span style="width:12px; height:12px; background:#ffbd2e; border-radius:50%; display:inline-block;"></span>
        <span style="width:12px; height:12px; background:#27c93f; border-radius:50%; display:inline-block;"></span>
        <span style="color:#888; margin-left:8px; font-size:12px;">employee-projects-terminal</span>
    </div>
    <div style="padding:20px; color:#33ff33; font-size:14px; line-height:1.6;">
        <p style="color:#888; margin:0 0 20px 0;">// Employee Common Projects Finder</p>

        <form method="POST" enctype="multipart/form-data" action="/" style="margin-bottom:20px;">
            @csrf
            <p style="color:#fff; margin:0 0 10px 0;">$ Select CSV file:</p>
<input type="file" name="file" accept=".csv" required 
                   style="background:#0c0c0c; color:#33ff33; border:1px solid #333; padding:10px; width:100%; box-sizing:border-box;">
            @if($errors->has('file'))
                <p style="color:#ff5555;">Error: {{ $errors->first('file') }}</p>
            @endif
            <button type="submit" style="background:#1a4d1a; color:#33ff33; border:1px solid #33ff33; padding:10px 30px; margin-top:15px; cursor:pointer; font-family:monospace;">
                &gt; PROCESS
            </button>
        </form>

        @if(isset($parseErrors) && !empty($parseErrors))
            <p style="color:#ff5555; margin-bottom:15px;">// Parse Errors:</p>
            <ul style="color:#ff5555; margin-bottom:15px;">
                @foreach($parseErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        @if(isset($results) && count($results) > 0)
            <p style="color:#888; margin-bottom:15px;">// Results (sorted by total days worked together)</p>
            <p style="margin-top:20px; color:#fff;">
                Longest pair: <span style="color:#33ff33;">{{ $results[0]['emp1'] }}, {{ $results[0]['emp2'] }}, {{ $results[0]['total_days'] }} days</span>
            </p>
            <table style="width:100%; border-collapse:collapse;">
                <tr style="border-bottom:1px solid #333;">
                    <th style="text-align:left; padding:8px; color:#fff;">Employee #1</th>
                    <th style="text-align:left; padding:8px; color:#fff;">Employee #2</th>
                    <th style="text-align:left; padding:8px; color:#fff;">Project ID</th>
                    <th style="text-align:left; padding:8px; color:#fff;">Days</th>
                </tr>
                @foreach($results as $pair)
                    @foreach($pair['projects'] as $project)
                        <tr style="border-bottom:1px solid #222;">
                            <td style="padding:8px;">{{ $pair['emp1'] }}</td>
                            <td style="padding:8px;">{{ $pair['emp2'] }}</td>
                            <td style="padding:8px;">{{ $project['project_id'] }}</td>
                            <td style="padding:8px;">{{ $project['days'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </table>
        @elseif(isset($results))
            <p style="color:#888;">No common projects found.</p>
        @endif
    </div>
</div>
</body>
</html>
