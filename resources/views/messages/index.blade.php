@extends('layouts.dashboard', ['sidebar' => 'student'])

@section('title', 'Messages')

@section('content')
<div class="mb-4">
    <h1 class="text-headline-md text-primary-rp mb-1">Messages</h1>
    <p class="text-body-md text-muted-rp">Communicate with recruiters and employers.</p>
</div>

<div class="rp-card rp-card-lg" style="height:calc(100vh - 200px);">
    <div class="row h-100 g-0">
        {{-- Conversation list --}}
        <div class="col-md-4 border-end h-100 overflow-auto">
            @foreach([
                ['name' => 'TechFlow HR', 'preview' => 'Your interview is scheduled for...', 'time' => '2h ago', 'unread' => true],
                ['name' => 'Analytics Pro', 'preview' => 'Thank you for your application...', 'time' => '1d ago', 'unread' => false],
                ['name' => 'Creative Studios', 'preview' => 'We would like to discuss your portfolio...', 'time' => '3d ago', 'unread' => false],
            ] as $conv)
            <div class="d-flex align-items-center gap-3 p-3 border-bottom {{ $conv['unread'] ? 'bg-surface-container-low' : '' }}" style="cursor:pointer;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($conv['name']) }}&background=1e293b&color=fff" class="rounded-circle" width="40" height="40" alt="">
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between">
                        <span class="text-body-md fw-semibold">{{ $conv['name'] }}</span>
                        <span class="text-label-md text-muted-rp">{{ $conv['time'] }}</span>
                    </div>
                    <p class="text-body-md text-muted-rp mb-0 text-truncate">{{ $conv['preview'] }}</p>
                </div>
                @if($conv['unread'])
                <span class="badge bg-primary rounded-pill">1</span>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Chat area --}}
        <div class="col-md-8 d-flex flex-column h-100">
            <div class="p-3 border-bottom d-flex align-items-center gap-3">
                <img src="https://ui-avatars.com/api/?name=TechFlow+HR&background=1e293b&color=fff" class="rounded-circle" width="40" height="40" alt="">
                <div>
                    <div class="text-body-md fw-semibold">TechFlow HR</div>
                    <div class="text-label-md text-success">Online</div>
                </div>
            </div>
            <div class="flex-grow-1 p-3 overflow-auto">
                <div class="mb-3">
                    <div class="bg-surface-container-low rounded p-3 d-inline-block" style="max-width:70%;">
                        <p class="text-body-md mb-1">Hi Alex! We've reviewed your application for the Software Engineer Intern position.</p>
                        <span class="text-label-md text-muted-rp">Yesterday, 3:45 PM</span>
                    </div>
                </div>
                <div class="mb-3 text-end">
                    <div class="bg-primary text-white rounded p-3 d-inline-block" style="max-width:70%;">
                        <p class="text-body-md mb-1">Thank you! I'm very excited about this opportunity.</p>
                        <span class="text-label-md opacity-75">Yesterday, 4:12 PM</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="bg-surface-container-low rounded p-3 d-inline-block" style="max-width:70%;">
                        <p class="text-body-md mb-1">Great! Your technical interview is scheduled for April 8th at 2:00 PM. Please confirm your availability.</p>
                        <span class="text-label-md text-muted-rp">2 hours ago</span>
                    </div>
                </div>
            </div>
            <div class="p-3 border-top">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Type a message..." id="message-input">
                    <button class="btn btn-rp-primary" type="button" id="send-message">
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('send-message')?.addEventListener('click', function() {
    const input = document.getElementById('message-input');
    if (input.value.trim()) {
        showToast('Message sent!', 'success');
        input.value = '';
    }
});
</script>
@endpush
@endsection
