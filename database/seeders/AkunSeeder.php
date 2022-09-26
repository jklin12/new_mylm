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
                'username' => 'hd01',
                'name' => 'hd01',
                'email' => 'hd01@lifemedia.id',
                'password' => bcrypt('hd01'),
                'level' => 8,
            ], 
            [
                'username' => 'ccare1',
                'name' => 'ccare1',
                'email' => 'ccare1@lifemedia.id',
                'password' => bcrypt('ccare1'),
                'level' => 8,
            ], 
        ];

        foreach ($user as $key => $value) {
            User::create($value);
        }
    }
}
