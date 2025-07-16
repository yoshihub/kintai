<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => '田中太郎',
                'email' => 'tanaka@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => '佐藤花子',
                'email' => 'sato@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => '鈴木一郎',
                'email' => 'suzuki@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => '高橋美咲',
                'email' => 'takahashi@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => '山田健太',
                'email' => 'yamada@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => '松本由美',
                'email' => 'matsumoto@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => '小林大輔',
                'email' => 'kobayashi@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ],
            [
                'name' => '加藤明美',
                'email' => 'kato@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
