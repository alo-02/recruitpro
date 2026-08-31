@php
    $score = (int) ($score ?? 0);
    $threshold = (int) ($threshold ?? 50);
    $pass = $score >= $threshold;
    $band = $score >= 75 ? 'success' : ($score >= 50 ? 'warning' : 'secondary');
    $matched = $matched ?? [];
    $missing = $missing ?? [];
    $summary = $summary ?? null;
    $source = $source ?? null;
    $compact = $compact ?? false;
@endphp
<div class="rp-match {{ $compact ? 'rp-match-compact' : '' }}">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="text-label-md text-muted-rp">AI match</span>
        <span class="badge bg-{{ $band }} {{ $band === 'warning' ? 'text-dark' : '' }}">{{ $score }}%</span>
    </div>
    <div class="rp-match-bar mb-2" role="progressbar" aria-valuenow="{{ $score }}" aria-valuemin="0" aria-valuemax="100">
        <span class="rp-match-fill bg-{{ $band === 'warning' ? 'warning' : $band }}" style="width: {{ $score }}%"></span>
    </div>
    @unless($compact)
        <div class="text-label-md text-muted-rp mb-2">
            {{ $pass ? 'At or above '.$threshold.'% threshold' : 'Below '.$threshold.'% threshold' }}
            @if($source === 'gemini')
                · Gemini
            @elseif($source === 'fallback')
                · Keyword fallback
            @endif
        </div>
        @if($summary)
            <p class="text-body-md mb-2">{{ $summary }}</p>
        @endif
        @if($matched || $missing)
            <div class="text-label-md text-muted-rp mb-1">Matched skills</div>
            @forelse($matched as $kw)
                <span class="badge bg-success-subtle text-success me-1 mb-1">{{ $kw }}</span>
            @empty
                <span class="text-muted-rp text-body-md">None</span>
            @endforelse
            <div class="text-label-md text-muted-rp mt-2 mb-1">Missing skills</div>
            @forelse($missing as $kw)
                <span class="badge bg-danger-subtle text-danger me-1 mb-1">{{ $kw }}</span>
            @empty
                <span class="text-muted-rp text-body-md">None</span>
            @endforelse
        @endif
    @endunless
</div>
