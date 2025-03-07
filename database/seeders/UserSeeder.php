<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Production Manager',
            'email' => 'pm@smwom.com',
            'password' => Hash::make('pm@smwom.com'),
            'role_id' => '1',
        ]);

        User::create([
            'name' => 'Operator 1',
            'email' => 'op1@smwom.com',
            'password' => Hash::make('op1@smwom.com'),
            'role_id' => '2',
        ]);

        User::create([
            'name' => 'Operator 2',
            'email' => 'op2@smwom.com',
            'password' => Hash::make('op2@smwom.com'),
            'role_id' => '2',
        ]);
    }
}
