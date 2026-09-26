<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function getCurrentUser()
    {
        return auth()->user();
    }

    private function getCurrentUserId(): ?int
    {
        $user = $this->getCurrentUser();
        return $user ? $user->id : null;
    }

    private function getCurrentUserType(): ?string
    {
        $user = $this->getCurrentUser();
        return $user ? $user->role : null;
    }

    private function getRoutePrefix(): string
    {
        $user = $this->getCurrentUser();

        return match ($user->role) {
            'midwife' => 'midwife',
            'bhw' => 'bhw',
            'bhw_president' => 'bhw-president',
            'user' => 'user',
            default => 'user',
        };
    }

    public function index()
    {
        $userId = $this->getCurrentUserId();
        $userType = $this->getCurrentUserType();
        if (!$userId || !$userType) {
            return redirect()->route('login');
        }

        $search = trim((string) request('search'));

        $messages = Message::with([
                'sender',
                'receiver',
                'replies.sender',
                'replies.receiver',
            ])
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->thread()
            ->withCount('replies')
            ->withMax('replies', 'created_at')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($messageQuery) use ($search) {
                    $messageQuery->where('subject', 'like', '%' . $search . '%')
                        ->orWhere('body', 'like', '%' . $search . '%')
                        ->orWhereHas('sender', fn ($q) => $q->where(function ($sq) use ($search) {
                            $sq->where('first_name', 'like', '%' . $search . '%')
                               ->orWhere('last_name', 'like', '%' . $search . '%');
                        }))
                        ->orWhereHas('receiver', fn ($q) => $q->where(function ($sq) use ($search) {
                            $sq->where('first_name', 'like', '%' . $search . '%')
                               ->orWhere('last_name', 'like', '%' . $search . '%');
                        }));
                });
            })
            // NOTE: cannot be COALESCE(replies_max_created_at, ...) — Postgres
            // resolves a SELECT alias only as a bare ORDER BY item, never
            // nested inside an expression (MySQL/SQLite allow it). Inline the
            // aggregate instead; identifiers stay unquoted/lowercase so the
            // same SQL parses on pgsql, mysql and sqlite.
            ->orderByRaw('COALESCE((select max(replies.created_at) from messages as replies where replies.reply_to_id = messages.id and replies.deleted_at is null), messages.created_at) DESC')
            ->paginate(15);

        $unreadCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        $contacts = $this->getContacts($search);

        return view('messages.index', compact('messages', 'unreadCount', 'contacts', 'search'));
    }

    public function create(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $contacts = $this->getContacts($search);
        $receiverId = $request->query('to');
        $receiverRole = $request->query('role');
        $receiver = ($receiverId && $receiverRole)
            ? $this->findUserByRoleAndId($receiverRole, (int) $receiverId)
            : null;

        if ($receiver && !$this->canMessage($this->getCurrentUser(), $receiver)) {
            $receiver = null;
        }

        return view('messages.create', compact('contacts', 'receiver', 'search'));
    }

    public function send(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return $request->wantsJson()
                ? response()->json(['success' => false], 401)
                : redirect()->route('login');
        }

        $request->validate([
            'receiver_id' => 'required|integer',
            'receiver_role' => 'required|in:woman,midwife,bhw,bhw_president',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:2000',
            'reply_to_id' => 'nullable|exists:messages,id',
            'client_uuid' => 'nullable|string|max:64',
        ]);

        $receiver = $this->findUserByRoleAndId($request->receiver_role, (int) $request->receiver_id);
        if (!$receiver) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid recipient.'], 422);
            }
            return back()->withErrors(['receiver_id' => 'The selected recipient is invalid.'])->withInput();
        }

        if (!$this->canMessage($currentUser, $receiver)) {
            throw ValidationException::withMessages([
                'receiver_id' => 'You are not allowed to message this user.',
            ]);
        }

        // Idempotency: double-tap / retry with the same client_uuid returns the original row.
        $clientUuid = $request->input('client_uuid');
        if ($clientUuid) {
            $existing = Message::withTrashed()->where('client_uuid', $clientUuid)
                ->where('sender_id', $currentUser->id)->first();
            if ($existing) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => $this->messagePayload($existing), 'deduped' => true]);
                }
                $prefix = $this->getRoutePrefix();
                $threadId = $existing->reply_to_id ?: $existing->id;
                return redirect()->route($prefix.'.messages.thread', $threadId)->with('success', 'Message sent successfully.');
            }
        }

        $message = Message::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => (int) $request->receiver_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'reply_to_id' => $request->reply_to_id,
            'client_uuid' => $clientUuid,
        ]);

        \App\Models\Notification::createNotification(
            (int) $request->receiver_id,
            'New Message from ' . $currentUser->name . ': ' . \Illuminate\Support\Str::limit($request->body, 60),
            'New Message',
            'info',
            '/' . $this->getRoutePrefixForRole($request->receiver_role) . '/messages/' . ($request->reply_to_id ?: $message->id),
            $request->receiver_role === 'woman' ? 'user' : $request->receiver_role
        );

        // SMS nudge for NEW threads only (replies skip SMS to avoid spamming
        // long conversations). Never breaks messaging if SMS fails.
        if (!$request->reply_to_id) {
            try {
                if ($receiver->hasSmsEnabled()) {
                    (new \App\Services\SmsService())->sendCustom(
                        $receiver,
                        'Hi ' . ($receiver->first_name ?? 'there') . ', you have a new ReproCare message from ' . $currentUser->name . ': ' . \Illuminate\Support\Str::limit($request->subject ?: $request->body, 100),
                        'Kumusta ' . ($receiver->first_name ?? '') . ', may bago kang mensahe sa ReproCare mula kay ' . $currentUser->name . '. Pakibuksan ang iyong account.'
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Message SMS nudge failed: ' . $e->getMessage());
            }
        }

        $successMsg = 'Message sent successfully.';
        $prefix = $this->getRoutePrefix();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $this->messagePayload($message->fresh())]);
        }

        if ($request->reply_to_id) {
            $successMsg = 'Reply sent.';
            return redirect()->route($prefix . '.messages.thread', $request->reply_to_id)
                ->with('success', $successMsg);
        }

        return redirect()->route($prefix . '.messages.thread', $message->id)
            ->with('success', $successMsg);
    }

    /**
     * Instagram-style live updates: new replies + read state since last seen.
     * GET /{role}/messages/{id}/updates?after_id=0
     */
    public function updates(Request $request, int $id)
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return response()->json(['success' => false], 401);
        }
        $root = Message::withTrashed()->findOrFail($id);
        if ($root->sender_id !== $userId && $root->receiver_id !== $userId) {
            abort(403);
        }
        $afterId = (int) $request->query('after_id', 0);
        $replies = Message::with('sender')
            ->where('reply_to_id', $root->id)
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')->limit(50)->get()
            ->map(fn ($m) => $this->messagePayload($m));
        // Mark peer messages as seen on poll (same as opening the thread).
        Message::where('reply_to_id', $root->id)
            ->where('receiver_id', $userId)->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        if ($root->receiver_id === $userId && !$root->is_read) {
            $root->markRead();
        }
        $root->refresh();
        return response()->json([
            'success' => true,
            'messages' => $replies,
            'root_read' => (bool) $root->is_read,
            'root_read_at' => optional($root->read_at)?->toIso8601String(),
            'last_id' => $replies->max('id') ?? $afterId,
        ]);
    }

    private function messagePayload(Message $m): array
    {
        return [
            'id' => $m->id,
            'body' => $m->body,
            'sender_id' => $m->sender_id,
            'sender_name' => $m->sender?->name,
            'created_at' => $m->created_at?->toIso8601String(),
            'time' => $m->created_at?->format('g:i A'),
            'is_read' => (bool) $m->is_read,
        ];
    }

    public function thread(int $id)
    {
        $userId = $this->getCurrentUserId();
        $userType = $this->getCurrentUserType();
        if (!$userId || !$userType) {
            return redirect()->route('login');
        }

        $root = Message::with([
                'sender',
                'receiver',
                'replies.sender',
                'replies.receiver',
            ])
            ->findOrFail($id);

        $isSender = $root->sender_id === $userId;
        $isReceiver = $root->receiver_id === $userId;

        if (!$isSender && !$isReceiver) {
            abort(403);
        }

        if ($isReceiver && !$root->is_read) {
            $root->markRead();
        }

        Message::where('reply_to_id', $root->id)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $contacts = $this->getContacts();

        return view('messages.thread', compact('root', 'contacts'));
    }

    public function markRead(int $id)
    {
        $userId = $this->getCurrentUserId();
        $userType = $this->getCurrentUserType();
        if (!$userId || !$userType) {
            return response()->json(['success' => false], 401);
        }

        $message = Message::where('receiver_id', $userId)
            ->findOrFail($id);

        $message->markRead();

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, int $id)
    {
        $userId = $this->getCurrentUserId();
        $userType = $this->getCurrentUserType();
        if (!$userId || !$userType) {
            return $request->wantsJson()
                ? response()->json(['success' => false], 401)
                : redirect()->route('login');
        }

        $message = Message::findOrFail($id);

        $isSender = $message->sender_id === $userId;
        $isReceiver = $message->receiver_id === $userId;

        if (!$isSender && !$isReceiver) {
            abort(403);
        }

        // Soft-delete only: deleting a thread root moves the whole conversation
        // to trash together so Restore brings everything back. Replies delete solo.
        \Illuminate\Support\Facades\DB::transaction(function () use ($message) {
            if ($message->reply_to_id === null) {
                Message::where('reply_to_id', $message->id)->delete();
            }
            $message->delete();
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'restore_id' => $message->id]);
        }

        return redirect()->route($this->getRoutePrefix().'.messages.index')
            ->with('success', 'Conversation moved to trash. You can restore it from Trash.')
            ->with('restore_id', $message->id);
    }

    public function trash()
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return redirect()->route('login');
        }
        $messages = Message::onlyTrashed()->with(['sender', 'receiver'])
            ->where(fn ($q) => $q->where('sender_id', $userId)->orWhere('receiver_id', $userId))
            ->thread()->latest()->paginate(15);
        $contacts = $this->getContacts();
        return view('messages.trash', compact('messages', 'contacts'));
    }

    public function restore(Request $request, int $id)
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $request->wantsJson()
                ? response()->json(['success' => false], 401)
                : redirect()->route('login');
        }
        $message = Message::onlyTrashed()->findOrFail($id);
        if ($message->sender_id !== $userId && $message->receiver_id !== $userId) {
            abort(403);
        }
        \Illuminate\Support\Facades\DB::transaction(function () use ($message) {
            $message->restore();
            if ($message->reply_to_id === null) {
                Message::onlyTrashed()->where('reply_to_id', $message->id)->restore();
            }
        });
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route($this->getRoutePrefix().'.messages.thread', $message->id)
            ->with('success', 'Conversation restored.');
    }

    private function getContacts(?string $search = null): Collection
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return collect([]);
        }

        $contacts = User::query()
            ->select(['id', 'first_name', 'middle_initial', 'last_name', 'role', 'status'])
            ->where('id', '!=', $currentUser->id)
            ->whereIn('role', ['user', 'midwife', 'bhw', 'bhw_president'])
            ->where(function ($query) {
                $query->where('role', '!=', 'user')
                    ->orWhere(function ($userQuery) {
                        $userQuery->where('role', 'user')
                            ->where(function ($approvedQuery) {
                                $approvedQuery->whereNull('status')
                                    ->orWhere('status', 'approved');
                            });
                    });
            })
            ->get()
            ->filter(fn (User $user) => $this->canMessage($currentUser, $user))
            ->map(function (User $user) {
                $messagingRole = $this->normalizeUserRoleForMessaging($user->role);

                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $messagingRole,
                    'type' => $messagingRole,
                    'user_role' => $user->role,
                    'label' => $this->formatRoleLabel($user->role),
                ];
            });

        return $this->filterContacts($contacts, $search);
    }

    private function filterContacts(\Illuminate\Support\Collection $contacts, ?string $search): \Illuminate\Support\Collection
    {
        if (!$search) {
            return $contacts->sortBy('name')->values();
        }

        $needle = mb_strtolower($search);

        return $contacts->filter(function ($contact) use ($needle) {
            return str_contains(mb_strtolower($contact->name), $needle)
                || str_contains(mb_strtolower($contact->role), $needle)
                || str_contains(mb_strtolower($contact->label ?? ''), $needle);
        })->sortBy('name')->values();
    }

    private function findUserByRoleAndId(string $role, int $id)
    {
        return match ($role) {
            'woman' => User::where('role', 'user')->find($id),
            'midwife' => User::where('role', 'midwife')->find($id),
            'bhw' => User::where('role', 'bhw')->find($id),
            'bhw_president' => User::where('role', 'bhw_president')->find($id),
            default => null,
        };
    }


    private function getRoutePrefixForRole(string $role): string
    {
        return match ($role) {
            'midwife' => 'midwife',
            'bhw' => 'bhw',
            'bhw_president' => 'bhw-president',
            default => 'user',
        };
    }

    private function canMessage(?User $sender, ?User $receiver): bool
    {
        if (!$sender || !$receiver) {
            return false;
        }

        if ($sender->id === $receiver->id) {
            return false;
        }

        if ($sender->role === 'user') {
            return in_array($receiver->role, ['midwife', 'bhw', 'bhw_president'], true);
        }

        return in_array($sender->role, ['midwife', 'bhw', 'bhw_president'], true)
            && in_array($receiver->role, ['user', 'midwife', 'bhw', 'bhw_president'], true);
    }

    private function normalizeUserRoleForMessaging(string $role): string
    {
        return $role === 'user' ? 'woman' : $role;
    }

    private function formatRoleLabel(string $role): string
    {
        return match ($role) {
            'user' => 'Patient',
            'midwife' => 'Midwife',
            'bhw_president' => 'BHW President',
            'bhw' => 'BHW',
            default => ucfirst(str_replace('_', ' ', $role)),
        };
    }
}
