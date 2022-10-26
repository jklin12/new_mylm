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
                'username' => 'noc01',
                'name' => 'noc01',
                'email' => 'noc@lifemedia.id',
                'password' => bcrypt('noc01'),
                'level' => 6,
            ],
        ];

        foreach ($user as $key => $value) {
            User::create($value);
        }
    }
}
