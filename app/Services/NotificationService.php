<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class NotificationService
{
    public function createForUser(
        int|User $user,
        string $type,
        string $title,
        string $message,
        ?string $url = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
        array $data = [],
        int|User|null $createdBy = null,
        int $dedupeSeconds = 30
    ): ?Notification {
        $userId = $user instanceof User ? (int) $user->id : (int) $user;
        if ($userId <= 0) {
            return null;
        }

        $dedupeKey = $data['dedupe_key'] ?? $this->dedupeKey($userId, $type, $relatedType, $relatedId, $title);
        $recentDuplicate = Notification::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('title', $title)
            ->where('created_at', '>=', now()->subSeconds($dedupeSeconds))
            ->when($relatedType, fn ($query) => $query->where('related_type', $relatedType))
            ->when($relatedId, fn ($query) => $query->where('related_id', $relatedId))
            ->where(function ($query) use ($dedupeKey) {
                $query->where('data->dedupe_key', $dedupeKey);
            })
            ->exists();

        if ($recentDuplicate) {
            return null;
        }

        $data['dedupe_key'] = $dedupeKey;

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'url' => $url,
            'created_by' => $createdBy instanceof User ? $createdBy->id : $createdBy,
        ]);
    }

    public function createForUsers(iterable $users, string $type, string $title, string $message, ?string $url = null, ?string $relatedType = null, ?int $relatedId = null, array $data = [], int|User|null $createdBy = null): void
    {
        collect($users)
            ->map(fn ($user) => $user instanceof User ? (int) $user->id : (int) $user)
            ->filter()
            ->unique()
            ->each(fn (int $userId) => $this->createForUser($userId, $type, $title, $message, $url, $relatedType, $relatedId, $data, $createdBy));
    }

    public function notifyClientNoteAssigned(Client $client, ClientActivity $note, int $assignedTo, ?User $actor = null): void
    {
        $clientName = trim($client->full_name) ?: ($client->company ?: 'client');

        $this->createForUser(
            $assignedTo,
            'note',
            'New note assigned',
            'A new note has been assigned to you for client ' . $clientName . '.',
            route('admin.clients.show', ['id' => $client->id, 'tab' => 'activities', 'activity_tab' => 'NOTES']),
            ClientActivity::class,
            $note->id,
            ['client_id' => $client->id],
            $actor
        );
    }

    public function notifyClientTaskAssigned(Client $client, ClientActivity $task, int $assignedTo, ?User $actor = null): void
    {
        $this->createForUser(
            $assignedTo,
            'task',
            'New task assigned',
            'You have a new task: ' . ($task->title ?: 'Task') . '.',
            route('admin.clients.show', ['id' => $client->id, 'tab' => 'activities', 'activity_tab' => 'TASKS']),
            ClientActivity::class,
            $task->id,
            ['client_id' => $client->id],
            $actor
        );
    }

    public function notifyReservationAssigned(Reservation $reservation, int $userId, ?User $actor = null): void
    {
        $date = $reservation->date ? \Carbon\Carbon::parse($reservation->date)->format('M d, Y') : 'the scheduled date';
        $time = $reservation->time ? \Carbon\Carbon::parse($reservation->time)->format('g:i A') : 'the scheduled time';

        $this->createForUser(
            $userId,
            'event',
            'Event assigned',
            'You have been assigned to a new event on ' . $date . ' at ' . $time . '.',
            route('staff.events.show', $reservation),
            Reservation::class,
            $reservation->id,
            ['reservation_code' => $reservation->code],
            $actor
        );
    }

    public function notifyReservationUpdated(Reservation $reservation, int $userId, ?User $actor = null): void
    {
        $this->createForUser(
            $userId,
            'event',
            'Assigned event updated',
            'Your assigned event has been updated.',
            route('staff.events.show', $reservation),
            Reservation::class,
            $reservation->id,
            ['reservation_code' => $reservation->code],
            $actor
        );
    }

    public function notifyReservationRemoved(Reservation $reservation, int $userId, ?User $actor = null): void
    {
        $this->createForUser(
            $userId,
            'staff_booking',
            'Removed from event',
            'You have been removed from an assigned event.',
            route('staff.dashboard'),
            Reservation::class,
            $reservation->id,
            ['reservation_code' => $reservation->code],
            $actor
        );
    }

    public function notifyInvoiceCancelled(Invoice $invoice, ?User $actor = null): void
    {
        $this->createForUsers(
            $this->adminOfficeUsers(),
            'invoice',
            'Invoice cancelled',
            'Invoice #' . ($invoice->invoice_number ?: $invoice->id) . ' has been cancelled.',
            route('admin.invoices.show', ['invoice' => $invoice->id]),
            Invoice::class,
            $invoice->id,
            [],
            $actor
        );
    }

    public function notifyInvoiceOverdue(Invoice $invoice): void
    {
        $this->createForUsers(
            $this->adminOfficeUsers(),
            'invoice',
            'Invoice overdue',
            'Invoice #' . ($invoice->invoice_number ?: $invoice->id) . ' is overdue and still has an open balance.',
            route('admin.invoices.show', ['invoice' => $invoice->id]),
            Invoice::class,
            $invoice->id,
            ['dedupe_key' => 'invoice_overdue_' . $invoice->id . '_' . now()->toDateString()],
            null
        );
    }

    public function notifyStaffConfirmed(Reservation $reservation, User $staff): void
    {
        $this->createForUsers(
            $this->adminOfficeUsers(),
            'staff_booking',
            'Event confirmed',
            $staff->name . ' confirmed their assigned event.',
            route('admin.reservations.show', ['id' => $reservation->id]),
            Reservation::class,
            $reservation->id,
            ['staff_user_id' => $staff->id]
        );
    }

    public function adminOfficeUsers(): EloquentCollection
    {
        return User::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereIn('role', ['owner', 'admin', 'manager', 'office'])
                    ->orWhereIn('staff_type', ['Owner', 'Admin', 'Manager', 'Office', 'owner', 'admin', 'manager', 'office']);
            })
            ->get(['id', 'name', 'role', 'staff_type']);
    }

    private function dedupeKey(int $userId, string $type, ?string $relatedType, ?int $relatedId, string $title): string
    {
        return sha1(implode('|', [$userId, $type, $relatedType ?: '', $relatedId ?: '', $title]));
    }
}
