<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {

        Admin::create([
            'firstname' => 'Louis',
            'lastname' => 'ALEXANDRE',
            'email' => 'louis@esper-impact.com',
            'tel' => '123456789',
            'country' => 'USA',
            'address' => '123 Main St',
            'city' => 'New York',
            'password' => Hash::make('Password@123'), 
        ]);

        Admin::create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'admin1@example.com',
            'tel' => '123456789',
            'country' => 'USA',
            'address' => '123 Main St',
            'city' => 'New York',
            'password' => Hash::make('Password@123'), 
        ]);

        Admin::create([
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'email' => 'admin2@example.com',
            'tel' => '987654321',
            'country' => 'Canada',
            'address' => '456 Elm St',
            'city' => 'Toronto',
            'password' => Hash::make('Password@123'),
        ]);

        Admin::create([
            'firstname' => 'Alice',
            'lastname' => 'Johnson',
            'email' => 'admin3@example.com',
            'tel' => '555555555',
            'country' => 'UK',
            'address' => '789 Oak St',
            'city' => 'London',
            'password' => Hash::make('Password@123'),
        ]);
    }
}
