<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class UploadedFiles
{
    public static function disk(): string
    {
        return (string) config('filesystems.uploads_disk', 'public');
    }

    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $disk = Storage::disk(self::disk());

        if ((bool) config('filesystems.uploads_temporary_urls', false)) {
            return $disk->temporaryUrl($path, now()->addMinutes(30));
        }

        return $disk->url($path);
    }
}
