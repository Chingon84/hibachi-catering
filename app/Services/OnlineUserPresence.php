<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OnlineUserPresence
{
    private const INDEX_KEY = 'online_user_presence:index';
    private const ACTIVE_MINUTES = 5;
    private const RETAIN_MINUTES = 10;

    public function mark(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $now = now();
        $activeSince = $now->copy()->subMinutes(self::ACTIVE_MINUTES)->timestamp;
        $expiresAt = $now->copy()->addMinutes(self::RETAIN_MINUTES);
        $presence = Cache::get(self::INDEX_KEY, []);

        if (!is_array($presence)) {
            $presence = [];
        }

        $presence = array_filter(
            $presence,
            fn ($lastSeen) => (int) $lastSeen >= $activeSince
        );
        $presence[(string) $userId] = $now->timestamp;

        Cache::put(self::INDEX_KEY, $presence, $expiresAt);
    }

    public function visibleFor(User $viewer): array
    {
        if (!$viewer->hasRole(['owner', 'admin'])) {
            return $this->emptyPayload();
        }

        $presence = $this->activePresence();
        if ($presence === []) {
            return $this->emptyPayload();
        }

        $photoColumn = $this->photoColumn();
        $columns = ['id', 'name', 'role', 'staff_type', 'position', 'is_active'];
        if ($photoColumn !== null) {
            $columns[] = $photoColumn;
        }

        $users = User::query()
            ->whereIn('id', array_map('intval', array_keys($presence)))
            ->whereKeyNot($viewer->id)
            ->where('is_active', true)
            ->get($columns)
            ->sortByDesc(fn (User $user) => (int) ($presence[(string) $user->id] ?? 0))
            ->values();

        $staff = [];
        $admins = [];

        foreach ($users as $user) {
            $entry = $this->formatUser($user, (int) ($presence[(string) $user->id] ?? now()->timestamp), $photoColumn);

            if ($user->hasRole(['owner', 'admin'])) {
                $admins[] = $entry;
            } else {
                $staff[] = $entry;
            }
        }

        return [
            'staff' => $staff,
            'admins' => $admins,
            'total' => count($staff) + count($admins),
            'active_window_seconds' => self::ACTIVE_MINUTES * 60,
        ];
    }

    private function activePresence(): array
    {
        $presence = Cache::get(self::INDEX_KEY, []);
        if (!is_array($presence)) {
            return [];
        }

        $activeSince = now()->subMinutes(self::ACTIVE_MINUTES)->timestamp;

        return array_filter(
            $presence,
            fn ($lastSeen) => (int) $lastSeen >= $activeSince
        );
    }

    private function formatUser(User $user, int $lastSeenAt, ?string $photoColumn): array
    {
        $secondsAgo = max(0, now()->timestamp - $lastSeenAt);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $this->roleLabel($user),
            'initials' => $this->initials((string) $user->name),
            'photo_url' => $this->photoUrl($user, $photoColumn),
            'last_seen_seconds' => $secondsAgo,
            'last_seen_label' => $this->lastSeenLabel($secondsAgo),
        ];
    }

    private function roleLabel(User $user): string
    {
        $role = trim((string) $user->role);
        $staffType = trim((string) $user->staff_type);
        $position = trim((string) $user->position);

        if ($staffType !== '') {
            return $staffType;
        }

        if ($position !== '') {
            return $position;
        }

        return $role !== '' ? Str::headline($role) : 'Staff';
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $initials .= strtoupper(substr($part, 0, 1));
            if (strlen($initials) >= 2) {
                break;
            }
        }

        return $initials !== '' ? $initials : 'U';
    }

    private function photoColumn(): ?string
    {
        foreach (['profile_photo_path', 'avatar_path', 'photo_path', 'avatar', 'photo'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function photoUrl(User $user, ?string $photoColumn): ?string
    {
        if ($photoColumn === null) {
            return null;
        }

        $path = trim((string) ($user->{$photoColumn} ?? ''));
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private function lastSeenLabel(int $secondsAgo): string
    {
        if ($secondsAgo < 60) {
            return 'Online now';
        }

        $minutes = max(1, (int) floor($secondsAgo / 60));

        return 'Last seen ' . $minutes . ' min ago';
    }

    private function emptyPayload(): array
    {
        return [
            'staff' => [],
            'admins' => [],
            'total' => 0,
            'active_window_seconds' => self::ACTIVE_MINUTES * 60,
        ];
    }
}
