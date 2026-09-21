<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seeded placeholder password for Adam's account. There is no
     * change-password screen in the MVP; change this via `php artisan tinker`
     * after first login if needed.
     */
    public const DEFAULT_PASSWORD = '50b7fb8d763c';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'adam'],
            [
                'name' => 'Адам',
                'phone' => '+79639892011',
                'email' => 'adam@crossfitgrozny.local',
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]
        );

        $this->command?->info('Admin account seeded — username: adam, password: '.self::DEFAULT_PASSWORD);
    }
}
