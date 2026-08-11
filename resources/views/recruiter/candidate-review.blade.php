@extends('layouts.dashboard', ['sidebar' => 'recruiter'])

@section('title', 'Candidate Review')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Candidate Review</h1>
    <p class="text-body-md text-muted-rp">Review and evaluate candidate applications.</p>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="rp-card rp-card-lg">
            <h2 class="text-title-lg text-primary-rp mb-3">Candidates</h2>
            @foreach([
                ['name' => 'Alex Kim', 'role' => 'SWE Intern', 'score' => 92, 'active' => true],
                ['name' => 'Sarah Chen', 'role' => 'Frontend Intern', 'score' => 88, 'active' => false],
                ['name' => 'Mike Johnson', 'role' => 'Data Intern', 'score' => 75, 'active' => false],
            ] as $c)
            <div class="d-flex align-items-center gap-3 p-2 rounded mb-1 {{ $c['active'] ? 'bg-surface-container-low border-start border-4 border-primary' : '' }}" style="cursor:pointer;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($c['name']) }}&background=0051d5&color=fff" class="rounded-circle" width="40" height="40" alt="">
                <div class="flex-grow-1">
                    <div class="text-body-md fw-semibold">{{ $c['name'] }}</div>
                    <div class="text-label-md text-muted-rp">{{ $c['role'] }}</div>
                </div>
                <span class="badge bg-success">{{ $c['score'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-8">
        <div class="rp-card rp-card-lg">
            <div class="d-flex align-items-start gap-4 mb-4">
                <img src="https://ui-avatars.com/api/?name=Alex+Kim&size=80&background=0051d5&color=fff" class="rounded-circle" width="80" height="80" alt="">
                <div class="flex-grow-1">
                    <h2 class="text-headline-md text-primary-rp mb-1">Alex Kim</h2>
                    <p class="text-body-md text-muted-rp mb-2">Software Engineering Student at State University</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="rp-tag">Java</span>
                        <span class="rp-tag">Spring</span>
                        <span class="rp-tag">AWS</span>
                        <span class="rp-tag">React</span>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-headline-lg text-success fw-bold">92</div>
                    <div class="text-label-md text-muted-rp">Match Score</div>
                </div>
            </div>

            <h3 class="text-title-lg text-primary-rp mb-3">Evaluation</h3>
            @foreach(['Technical Skills' => 4, 'Communication' => 5, 'Culture Fit' => 4, 'Experience' => 3] as $criteria => $rating)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-body-md">{{ $criteria }}</span>
                <div>
                    @for($i = 1; $i <= 5; $i++)
                    <span class="material-symbols-outlined {{ $i <= $rating ? 'text-warning' : 'text-muted-rp' }}" style="font-size:20px;{{ $i <= $rating ? "font-variation-settings:'FILL' 1;" : '' }}">star</span>
                    @endfor
                </div>
            </div>
            @endforeach

            <div class="mb-4 mt-3">
                <label class="form-label text-label-md text-muted-rp">Notes</label>
                <textarea class="form-control" rows="3" placeholder="Add evaluation notes...">Strong technical background. Good communication during phone screen. Recommend for technical interview.</textarea>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-rp-primary" onclick="showToast('Candidate advanced to interview!', 'success')">Advance to Interview</button>
                <button class="btn btn-rp-outline">Save Notes</button>
                <button class="btn btn-outline-danger ms-auto">Reject</button>
            </div>
        </div>
    </div>
</div>
@endsection
