<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RecruitPro Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; padding: 24px; color: #111; font-size: 12px; }
        h1 { margin-bottom: 4px; font-size: 20px; }
        h2 { margin-top: 24px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>RecruitPro Recruitment Report</h1>
    <p>Generated: {{ $stats['generated_at'] }}</p>

    <table>
        <tr><th>Metric</th><th>Value</th></tr>
        <tr><td>Users</td><td>{{ $stats['users'] }}</td></tr>
        <tr><td>Companies</td><td>{{ $stats['companies'] }}</td></tr>
        <tr><td>Jobs</td><td>{{ $stats['jobs'] }}</td></tr>
        <tr><td>Applications</td><td>{{ $stats['applications'] }}</td></tr>
        <tr><td>Hires</td><td>{{ $stats['hires'] }}</td></tr>
    </table>

    <h2>Applications by Status</h2>
    <table>
        <tr><th>Status</th><th>Count</th></tr>
        @foreach($byStatus as $row)
            <tr><td>{{ is_object($row->status) ? $row->status->value : $row->status }}</td><td>{{ $row->total }}</td></tr>
        @endforeach
    </table>

    @isset($byCategory)
    <h2>Jobs by Category</h2>
    <table>
        <tr><th>Category</th><th>Count</th></tr>
        @foreach($byCategory as $row)
            <tr><td>{{ $row->category }}</td><td>{{ $row->total }}</td></tr>
        @endforeach
    </table>
    @endisset
</body>
</html>
