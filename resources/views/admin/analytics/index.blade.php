@extends('layouts.dashboard', ['sidebar' => 'admin'])
@section('title', 'Analytics')
@section('content')
<div class="d-flex justify-content-between mb-4">
  <h1 class="text-headline-md text-primary-rp mb-0">Analytics Dashboard</h1>
  <a href="{{ route('admin.audit.export') }}" class="btn btn-rp-outline">Export Audit CSV</a>
</div>
<div class="row g-3 mb-4">
@foreach($stats as $k=>$v)
<div class="col-6 col-md-4 col-lg-2"><div class="stat-card"><div class="stat-value">{{ $v }}</div><div class="text-label-md text-muted-rp">{{ str_replace('_',' ', $k) }}</div></div></div>
@endforeach
</div>
<div class="rp-card rp-card-lg p-3">
  <h2 class="text-title-lg mb-3">Hiring Funnel</h2>
  @php $max = max(1, max($funnel)); @endphp
  @foreach($funnel as $stage => $count)
  <div class="mb-3">
    <div class="d-flex justify-content-between"><span>{{ ucfirst($stage) }}</span><strong>{{ $count }}</strong></div>
    <div class="progress" style="height:10px"><div class="progress-bar" style="width:{{ ($count/$max)*100 }}%"></div></div>
  </div>
  @endforeach
</div>
@endsection
