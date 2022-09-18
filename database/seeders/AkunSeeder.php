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
                'username' => 'aida',
                'name' => 'aida',
                'email' => 'aida@lifemedia.id',
                'password' => bcrypt('aida'),
                'level' => 8,
            ], 
        ];

        foreach ($user as $key => $value) {
            User::create($value);
        }
    }
}
