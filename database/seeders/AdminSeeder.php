<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        $admin = User::where('email', 'pera@admin.com')->first()
            ?? User::factory()->admin()->make([
                'first_name' => 'Pera',
                'last_name' => 'Admin',
                'email' => 'pera@admin.com',
            ]);

        $admin->forceFill([
            'is_admin' => 1,
            'email_verified_at' => $admin->email_verified_at ?? now(),
            'password' => Hash::make('test1234'),
        ])->save();
    }
}
