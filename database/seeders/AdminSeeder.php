<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@local');
        $username = env('ADMIN_USERNAME', 'admin');
        $name = env('ADMIN_NAME', 'Admin');
        $passEnv = env('ADMIN_PASSWORD'); // null if not set

        $base = [
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'position' => 'Owner',
            'role' => 'owner',
            'can_access_admin' => true,
            'is_active' => true,
        ];

        // Find by email first, then by username if provided
        $user = User::where('email', $email)->first();
        if (!$user && $username) {
            $user = User::where('username', $username)->first();
        }

        if ($user) {
            $user->fill($base);
            if (!empty($passEnv)) {
                $user->password = $passEnv; // casts() hashes
            }
            $user->save();
        } else {
            if (empty($passEnv)) {
                $passEnv = 'secret123';
            }
            $base['password'] = $passEnv; // casts() hashes
            User::create($base);
        }
    }
}
