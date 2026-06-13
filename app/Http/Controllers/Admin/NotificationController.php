<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    private const TYPE_FILTERS = [
        'event' => ['event', 'staff_booking'],
        'task' => ['task'],
        'note' => ['note'],
        'invoice' => ['invoice'],
        'employee' => ['employee'],
    ];

    public function index(Request $request): View
    {
        $status = strtolower((string) $request->query('status', 'all'));
        if (!in_array($status, ['all', 'unread', 'read'], true)) {
            $status = 'all';
        }

        $type = strtolower((string) $request->query('type', 'all'));
        if ($type !== 'all' && !array_key_exists($type, self::TYPE_FILTERS)) {
            $type = 'all';
        }

        $q = trim((string) $request->query('q', ''));

        $notifications = $this->baseQuery($request)
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($type !== 'all', fn ($query) => $query->whereIn('type', self::TYPE_FILTERS[$type]))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('message', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'status' => $status,
            'type' => $type,
            'q' => $q,
            'unreadCount' => $this->baseQuery($request)->whereNull('read_at')->count(),
            'typeOptions' => [
                'all' => 'All types',
                'event' => 'Events',
                'task' => 'Tasks',
                'note' => 'Notes',
                'invoice' => 'Invoices',
                'employee' => 'Employees',
            ],
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $notifications = $this->baseQuery($request)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Notification $notification) => $this->notificationPayload($notification))
            ->values();

        return response()->json([
            'unread' => $this->baseQuery($request)->whereNull('read_at')->count(),
            'notifications' => $notifications,
            'read_all_url' => route('admin.notifications.read-all'),
            'index_url' => route('admin.notifications.index'),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread' => $this->baseQuery($request)->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        $this->authorizeNotification($request, $notification);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('ok', 'Notification marked as read.');
    }

    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        $this->baseQuery($request)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('ok', 'All notifications marked as read.');
    }

    public function destroy(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        $this->authorizeNotification($request, $notification);
        $notification->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('ok', 'Notification deleted.');
    }

    private function baseQuery(Request $request)
    {
        return Notification::query()
            ->where('user_id', $request->user()->id);
    }

    private function authorizeNotification(Request $request, Notification $notification): void
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);
    }

    private function notificationPayload(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'url' => $notification->url,
            'read_at' => optional($notification->read_at)->toIso8601String(),
            'created_at' => optional($notification->created_at)->toIso8601String(),
            'created_label' => $notification->created_at?->diffForHumans() ?? '',
            'read_url' => route('admin.notifications.read', ['notification' => $notification->id]),
            'delete_url' => route('admin.notifications.destroy', ['notification' => $notification->id]),
            'icon' => $this->iconForType((string) $notification->type),
        ];
    }

    private function iconForType(string $type): string
    {
        return match ($type) {
            'event', 'staff_booking' => 'calendar',
            'task' => 'check',
            'note' => 'note',
            'invoice' => 'invoice',
            'employee' => 'user',
            default => 'bell',
        };
    }
}
