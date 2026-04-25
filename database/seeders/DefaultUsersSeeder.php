<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed initial AMMRK users.
 *
 * Reference: .kiro/specs/ammrk-platform/tasks.md (Task 2.4)
 */
class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'super-admin@ammrk.com',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@ammrk.com',
                'role' => 'Admin',
            ],
        ];

        foreach ($users as $row) {
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('Ammrk@2026'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($row['role'])) {
                $user->assignRole($row['role']);
            }
        }
    }
}
