<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            [
                'username' => 'qontak_dev',
                'name' => 'Qontak Dev',
                'email' => 'qontak_dev@mail.com',
                'password' => bcrypt('qontak_dev'),
                'api_token' => Str::random(60),
            ],
            
        ];

        foreach ($user as $key => $value) {
            User::create($value);
        }
    }
}
