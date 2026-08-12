@extends('layouts.dashboard', ['sidebar' => auth()->user()->isStudent() ? 'student' : (auth()->user()->isAdmin() ? 'admin' : 'recruiter')])

@section('title', 'Messages')

@section('content')
<div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h1 class="text-headline-md text-primary-rp mb-1">Messages</h1>
        <p class="text-body-md text-muted-rp mb-0">Realtime messaging {{ config('broadcasting.default') === 'reverb' ? '(Reverb live)' : '(polling-ready)' }}.</p>
    </div>
    @if(($composeTargets ?? collect())->isNotEmpty())
    <button class="btn btn-rp-primary" type="button" data-bs-toggle="collapse" data-bs-target="#compose-message">New Message</button>
    @endif
</div>

@if(($composeTargets ?? collect())->isNotEmpty())
<div class="collapse mb-4" id="compose-message">
    <div class="rp-card rp-card-lg p-3">
        <form method="POST" action="{{ route('messages.start') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-label-md">To</label>
                    <select name="application_id" id="compose-target" class="form-select" required>
                        <option value="">Select contact</option>
                        @foreach($composeTargets as $target)
                            <option value="{{ $target['application_id'] }}" data-user-id="{{ $target['id'] }}">{{ $target['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="user_id" id="compose-user-id" value="">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-label-md">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Subject">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-label-md">Message</label>
                    <input type="text" name="body" class="form-control" required placeholder="Write your message...">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-rp-primary w-100">Send</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="rp-card rp-card-lg" style="min-height:520px;">
    <div class="row g-0" style="min-height:520px;">
        <div class="col-md-4 border-end overflow-auto" style="max-height:520px;">
            @forelse($conversations as $conv)
                @php $other = $conv->otherParticipant(auth()->user()); @endphp
                <a href="{{ route('messages.show', $conv) }}" class="d-flex align-items-center gap-3 p-3 border-bottom text-decoration-none text-reset {{ isset($active) && $active->id === $conv->id ? 'bg-surface-container-low' : '' }}">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($other?->name ?? 'User') }}&background=1e293b&color=fff" class="rounded-circle" width="40" height="40" alt="">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between">
                            <span class="text-body-md fw-semibold">{{ $other?->name ?? 'Conversation' }}</span>
                            <span class="text-label-md text-muted-rp">{{ $conv->updated_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-body-md text-muted-rp mb-0 text-truncate">{{ $conv->messages->first()?->body ?? $conv->subject }}</p>
                    </div>
                </a>
            @empty
                <div class="p-4 text-muted-rp">No conversations yet.</div>
            @endforelse
        </div>

        <div class="col-md-8 d-flex flex-column" style="min-height:520px;">
            @isset($active)
                @php $other = $active->otherParticipant(auth()->user()); @endphp
                <div class="p-3 border-bottom">
                    <div class="text-body-md fw-semibold">{{ $other?->name }}</div>
                    <div class="text-label-md text-muted-rp">{{ $active->subject }}</div>
                </div>
                <div id="message-thread" class="flex-grow-1 p-3 overflow-auto" style="max-height:380px;" data-conversation-id="{{ $active->id }}" data-user-id="{{ auth()->id() }}">
                    @foreach($active->messages as $message)
                        <div class="mb-3 {{ $message->user_id === auth()->id() ? 'text-end' : '' }}" data-message-id="{{ $message->id }}">
                            <div class="d-inline-block rounded p-3 {{ $message->user_id === auth()->id() ? 'bg-primary text-white' : 'bg-surface-container-low' }}" style="max-width:75%;">
                                <p class="mb-1">{{ $message->body }}</p>
                                <span class="text-label-md opacity-75">{{ $message->created_at->format('M d, g:i A') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <form id="message-form" method="POST" action="{{ route('messages.store', $active) }}" class="p-3 border-top d-flex gap-2">
                    @csrf
                    <input type="text" name="body" id="message-body" class="form-control" placeholder="Type a message..." required autocomplete="off">
                    <button class="btn btn-rp-primary">Send</button>
                </form>
            @else
                <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted-rp p-4">
                    Select a conversation or start a new message with a recruiter/applicant.
                </div>
            @endisset
        </div>
    </div>
</div>
@endsection

@isset($active)
@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
(function () {
    const thread = document.getElementById('message-thread');
    const form = document.getElementById('message-form');
    const bodyInput = document.getElementById('message-body');
    if (!thread || !form) return;

    const conversationId = thread.dataset.conversationId;
    const currentUserId = Number(thread.dataset.userId);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function appendMessage(payload) {
        if (document.querySelector(`[data-message-id="${payload.id}"]`)) return;
        const mine = Number(payload.user_id) === currentUserId;
        const wrap = document.createElement('div');
        wrap.className = 'mb-3' + (mine ? ' text-end' : '');
        wrap.dataset.messageId = payload.id;
        wrap.innerHTML = `<div class="d-inline-block rounded p-3 ${mine ? 'bg-primary text-white' : 'bg-surface-container-low'}" style="max-width:75%;">
            <p class="mb-1"></p>
            <span class="text-label-md opacity-75">${payload.created_at ? new Date(payload.created_at).toLocaleString() : 'now'}</span>
        </div>`;
        wrap.querySelector('p').textContent = payload.body;
        thread.appendChild(wrap);
        thread.scrollTop = thread.scrollHeight;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const body = bodyInput.value.trim();
        if (!body) return;
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ body })
        });
        if (res.ok) {
            const data = await res.json();
            appendMessage(data.message);
            bodyInput.value = '';
        } else {
            form.submit();
        }
    });

    const reverbKey = @json(env('REVERB_APP_KEY'));
    const reverbHost = @json(env('REVERB_HOST', 'localhost'));
    const reverbPort = @json((int) env('REVERB_PORT', 8080));
    const reverbScheme = @json(env('REVERB_SCHEME', 'http'));

    if (reverbKey && window.Echo) {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: reverbPort,
            wssPort: reverbPort,
            forceTLS: reverbScheme === 'https',
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': csrf } }
        });

        window.Echo.private(`conversation.${conversationId}`)
            .listen('.message.sent', (e) => appendMessage(e));
    }
})();
</script>
@endpush
@endisset

@push('scripts')
<script>
document.getElementById('compose-target')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    document.getElementById('compose-user-id').value = opt?.dataset?.userId || '';
});
</script>
@endpush
