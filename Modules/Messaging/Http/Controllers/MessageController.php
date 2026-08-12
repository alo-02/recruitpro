<?php

namespace Modules\Messaging\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageReceived;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeMessages($request);

        $conversations = $request->user()
            ->conversations()
            ->with(['participants', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->get();

        $composeTargets = $this->composeTargets($request->user());

        return view('messages.index', compact('conversations', 'composeTargets'));
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorizeMessages($request);
        abort_unless($conversation->participants()->where('user_id', $request->user()->id)->exists(), 403);

        $conversation->load(['participants', 'messages.sender']);
        $conversation->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        $conversations = $request->user()
            ->conversations()
            ->with(['participants', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->get();

        $composeTargets = $this->composeTargets($request->user());

        return view('messages.index', [
            'conversations' => $conversations,
            'active' => $conversation,
            'composeTargets' => $composeTargets,
        ]);
    }

    public function store(Request $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        $this->authorizeMessages($request);
        abort_unless($conversation->participants()->where('user_id', $request->user()->id)->exists(), 403);

        $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        $conversation->touch();

        $recipient = $conversation->participants()->where('users.id', '!=', $request->user()->id)->first();
        if ($recipient) {
            $recipient->notify(new NewMessageReceived($message->load('sender')));
        }

        broadcast(new MessageSent($message))->toOthers();
        ActivityLogger::log('message.sent', $message);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'user_id' => $message->user_id,
                    'sender_name' => $request->user()->name,
                    'created_at' => $message->created_at->toIso8601String(),
                ],
            ]);
        }

        return redirect()->route('messages.show', $conversation);
    }

    public function start(Request $request): RedirectResponse
    {
        $this->authorizeMessages($request);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'application_id' => ['nullable', 'exists:applications,id'],
            'body' => ['required', 'string', 'max:5000'],
            'subject' => ['nullable', 'string', 'max:255'],
        ]);

        $other = User::findOrFail($request->user_id);
        abort_if($other->id === $request->user()->id, 422);

        $this->assertCanMessage($request->user(), $other, $request->application_id);

        if ($request->application_id) {
            $application = Application::with('jobPosting')->findOrFail($request->application_id);
            $conversation = $application->conversation;

            if (! $conversation) {
                $conversation = Conversation::create([
                    'subject' => $request->subject ?: ('Re: '.$application->jobPosting->title),
                    'application_id' => $application->id,
                ]);
                $conversation->participants()->attach([$request->user()->id, $other->id]);
            }
        } else {
            $conversation = $this->findOrCreateDirectConversation($request->user(), $other, $request->subject);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        $conversation->touch();
        $other->notify(new NewMessageReceived($message->load('sender')));
        broadcast(new MessageSent($message))->toOthers();
        ActivityLogger::log('conversation.started', $conversation);

        return redirect()->route('messages.show', $conversation)->with('success', 'Message sent.');
    }

    protected function authorizeMessages(Request $request): void
    {
        abort_unless($request->user()?->can('messages.use') || $request->user()?->isAdmin(), 403);
    }

    protected function assertCanMessage(User $from, User $to, ?int $applicationId): void
    {
        if ($from->isAdmin()) {
            return;
        }

        if ($applicationId) {
            $application = Application::with('jobPosting')->findOrFail($applicationId);
            $ok = ($from->id === $application->user_id && $to->id === $application->jobPosting->user_id)
                || ($from->id === $application->jobPosting->user_id && $to->id === $application->user_id);
            abort_unless($ok, 403);

            return;
        }

        if ($from->isStudent() && $to->isRecruiter()) {
            $linked = Application::query()
                ->where('user_id', $from->id)
                ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $to->id))
                ->exists();
            abort_unless($linked, 403);

            return;
        }

        if ($from->isRecruiter() && $to->isStudent()) {
            $linked = Application::query()
                ->where('user_id', $to->id)
                ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $from->id))
                ->exists();
            abort_unless($linked, 403);

            return;
        }

        abort(403);
    }

    protected function findOrCreateDirectConversation(User $from, User $to, ?string $subject): Conversation
    {
        $existing = Conversation::query()
            ->whereNull('application_id')
            ->whereHas('participants', fn ($q) => $q->where('users.id', $from->id))
            ->whereHas('participants', fn ($q) => $q->where('users.id', $to->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        $conversation = Conversation::create([
            'subject' => $subject ?: 'Direct message',
        ]);
        $conversation->participants()->attach([$from->id, $to->id]);

        return $conversation;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id:int,label:string,application_id:?int}>
     */
    protected function composeTargets(User $user)
    {
        if ($user->isStudent()) {
            return Application::query()
                ->with('jobPosting.recruiter')
                ->where('user_id', $user->id)
                ->latest('applied_at')
                ->get()
                ->map(fn (Application $app) => [
                    'id' => $app->jobPosting->user_id,
                    'label' => ($app->jobPosting->recruiter?->name ?? 'Recruiter').' · '.$app->jobPosting->title,
                    'application_id' => $app->id,
                ])
                ->unique('application_id')
                ->values();
        }

        if ($user->isRecruiter()) {
            return Application::query()
                ->with('applicant')
                ->whereHas('jobPosting', fn ($q) => $q->where('user_id', $user->id))
                ->latest('applied_at')
                ->get()
                ->map(fn (Application $app) => [
                    'id' => $app->user_id,
                    'label' => ($app->applicant?->name ?? 'Applicant').' · '.$app->jobPosting->title,
                    'application_id' => $app->id,
                ])
                ->unique('application_id')
                ->values();
        }

        return collect();
    }
}
