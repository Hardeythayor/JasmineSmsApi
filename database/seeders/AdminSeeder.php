<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = new User();
        $admin->name = 'Admin';
        $admin->userid = 'admin';
        $admin->email = 'admin@jasminesms.com';
        $admin->user_type = 'admin';
        $admin->email_verified_at = now();
        $admin->password = Hash::make('password'); // password
        $admin->save();
    }
}
