<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>{{ $user->name }} Resume</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111;padding:28px}h1{font-size:22px;margin:0}h2{font-size:14px;border-bottom:1px solid #ddd;margin-top:18px}ul{padding-left:16px}</style>
</head>
<body>
<h1>{{ $user->name }}</h1>
<p>{{ $profile?->headline }} · {{ $user->email }} · {{ $profile?->phone }} · {{ $profile?->location }}</p>
@if($profile?->bio)<h2>Summary</h2><p>{{ $profile->bio }}</p>@endif
@if($profile?->skills)<h2>Skills</h2><p>{{ implode(', ', $profile->skills) }}</p>@endif
<h2>Education</h2>
@foreach($profile?->educationEntries() ?? [] as $edu)
<p><strong>{{ $edu['degree'] ?? '' }}</strong> — {{ $edu['school'] ?? '' }} ({{ $edu['graduation_year'] ?? '' }})</p>
@endforeach
<h2>Experience</h2>
@foreach($profile?->experienceEntries() ?? [] as $exp)
<p><strong>{{ $exp['title'] ?? '' }}</strong> at {{ $exp['company'] ?? '' }} ({{ $exp['years'] ?? '' }})<br>{{ $exp['details'] ?? '' }}</p>
@endforeach
@if($projects->count())
<h2>Projects</h2>
@foreach($projects as $p)
<p><strong>{{ $p->title }}</strong> — {{ $p->description }} @if($p->url)({{ $p->url }})@endif</p>
@endforeach
@endif
</body></html>
