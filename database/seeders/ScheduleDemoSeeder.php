<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ScheduleDemoSeeder extends Seeder
{
    private const CHEFS = [
        'Jonathan E',
        'Elvis',
        'Marco H',
        'Said',
        'Ariel',
        'Angel',
        'Agustin',
        'Isaac',
        'Carlos',
        'Yaveth',
        'Mr. Agustin',
        'Eric',
        'Manuel',
        'Max',
    ];

    public function run(): void
    {
        foreach (self::CHEFS as $index => $name) {
            $email = 'schedule-demo-' . ($index + 1) . '@hibachi.local';

            User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => str()->slug($name, '.'),
                    'password' => Hash::make('password'),
                    'staff_type' => 'Chef',
                    'role' => 'staff',
                    'can_access_admin' => false,
                    'is_active' => true,
                ]
            );
        }
    }
}
