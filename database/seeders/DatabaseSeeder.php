<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(ActivitySeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(CompanyMemberSeeder::class);
        $this->call(ContactSeeder::class);
    }
}
