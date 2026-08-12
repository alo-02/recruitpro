@extends('layouts.dashboard', ['sidebar' => 'admin'])

@section('title', 'Company Approvals')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Companies</h1>
    <p class="text-body-md text-muted-rp">Approve or reject company registrations.</p>
</div>

<form method="GET" class="mb-3">
    <select name="status" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
        <option value="">All</option>
        @foreach(['pending','approved','rejected'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
        @endforeach
    </select>
</form>

<div class="rp-card rp-card-lg">
    <div class="table-responsive">
        <table class="table rp-table mb-0">
            <thead><tr><th>Company</th><th>Owner</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($companies as $company)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $company->name }}</div>
                        <div class="text-muted-rp text-label-md">{{ $company->website }}</div>
                    </td>
                    <td>{{ $company->owner?->name }}</td>
                    <td><span class="badge {{ $company->status->badgeClass() }}">{{ $company->status->label() }}</span></td>
                    <td>
                        @if($company->status->value === 'pending')
                        <form method="POST" action="{{ route('admin.companies.moderate', $company) }}" class="d-inline">@csrf @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.companies.moderate', $company) }}" class="d-inline">@csrf @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <input type="hidden" name="rejection_reason" value="Incomplete company information">
                            <button class="btn btn-sm btn-outline-danger">Reject</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $companies->links() }}</div>
</div>
@endsection
