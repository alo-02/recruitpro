<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Candidate</th>
                    <th>Score</th>
                    <th>Matched</th>
                    <th>Partial</th>
                    <th>Missing</th>
                    <th>Your decision</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $row)
                @php $decision = $row->latestDecision(); @endphp
                <tr>
                    <td class="fw-semibold">#{{ $row->rank }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $row->candidate->avatarUrl() }}" class="rp-avatar rounded-circle" width="36" height="36" alt="">
                            <div>
                                <div class="fw-semibold">{{ $row->candidate->name }}</div>
                                <div class="text-label-md text-muted-rp">{{ $row->candidate->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $row->overall_score >= 75 ? 'bg-success' : ($row->overall_score >= 50 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ $row->overall_score }}%
                        </span>
                        @if($row->ai_recommended)
                            <div class="text-label-md text-success mt-1">AI shortlist</div>
                        @endif
                    </td>
                    <td style="max-width:160px;">
                        @forelse(array_slice($row->matched_points ?? [], 0, 3) as $point)
                            <span class="badge bg-success-subtle text-success me-1 mb-1">{{ $point['label'] }}</span>
                        @empty
                            <span class="text-muted-rp">—</span>
                        @endforelse
                    </td>
                    <td style="max-width:160px;">
                        @forelse(array_slice($row->partial_matches ?? [], 0, 3) as $point)
                            <span class="badge bg-warning-subtle text-dark me-1 mb-1">{{ $point['label'] }}</span>
                        @empty
                            <span class="text-muted-rp">—</span>
                        @endforelse
                    </td>
                    <td style="max-width:160px;">
                        @forelse(array_slice($row->missing_points ?? [], 0, 3) as $point)
                            <span class="badge bg-danger-subtle text-danger me-1 mb-1">{{ $point['label'] }}</span>
                        @empty
                            <span class="text-muted-rp">—</span>
                        @endforelse
                    </td>
                    <td>
                        @if($decision)
                            <span class="badge bg-light text-dark">{{ $decision->decision->label() }}</span>
                        @else
                            <span class="text-muted-rp">Pending review</span>
                        @endif
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('screening.candidate', [$session, $row]) }}" class="btn btn-rp-outline btn-sm">Review</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted-rp py-4">{{ $empty }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
