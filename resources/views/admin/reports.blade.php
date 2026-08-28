@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'Reports')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <div>
        <h1 class="text-headline-md text-primary-rp mb-1">Recruitment Reports</h1>
        <p class="text-body-md text-muted-rp mb-0">Analytics and exportable reports.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.export') }}" class="btn btn-rp-outline">Export CSV</a>
        <a href="{{ route('admin.reports.pdf') }}" class="btn btn-rp-primary">Download PDF</a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($stats as $label => $value)
    <div class="col-6 col-lg">
        <div class="stat-card">
            <div class="stat-value">{{ $value }}</div>
            <div class="text-label-md text-muted-rp">{{ ucfirst($label) }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg mb-3">Applications by Status</h2>
            @forelse($byStatus as $status => $total)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                    <strong>{{ $total }}</strong>
                </div>
            @empty
                <p class="text-muted-rp">No data</p>
            @endforelse
        </div>
    </div>
    <div class="col-md-6">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg mb-3">Jobs by Type</h2>
            @forelse($byJobType as $type => $total)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ ucfirst(str_replace('-', ' ', $type)) }}</span>
                    <strong>{{ $total }}</strong>
                </div>
            @empty
                <p class="text-muted-rp">No data</p>
            @endforelse
        </div>
    </div>
</div>

@if(isset($monthlyApplications) && $monthlyApplications->isNotEmpty())
<div class="rp-card rp-card-lg mt-4">
    <h2 class="text-title-lg mb-3">Applications by Month</h2>
    @foreach($monthlyApplications as $month => $total)
        <div class="d-flex justify-content-between border-bottom py-2">
            <span>{{ $month }}</span>
            <strong>{{ $total }}</strong>
        </div>
    @endforeach
</div>
@endif
@endsection
