<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientPhotoController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $data = $request->validate([
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic', 'max:10240'],
        ]);

        foreach ($data['photos'] as $file) {
            $safeName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs("clients/{$client->id}/photos", $safeName, 'public');

            ClientPhoto::create([
                'client_id' => $client->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        return redirect()
            ->route('admin.clients.show', ['id' => $client->id, 'tab' => 'overview'])
            ->with('ok', 'Photos uploaded successfully.');
    }

    public function destroy(Client $client, ClientPhoto $photo)
    {
        if ($photo->client_id !== $client->id) {
            abort(404);
        }

        if (!empty($photo->path)) {
            Storage::disk('public')->delete($photo->path);
        }

        $photo->delete();

        return redirect()
            ->route('admin.clients.show', ['id' => $client->id, 'tab' => 'overview'])
            ->with('ok', 'Photo removed.');
    }
}
