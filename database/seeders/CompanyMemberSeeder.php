<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyMember;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanyMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::first();

        $members = [
            ['name' => 'Jean Dupont', 'email' => 'jean.dupont@example.com', 'password' => 'password123'],
            ['name' => 'Marie Martin', 'email' => 'marie.martin@example.com', 'password' => 'password123'],
            ['name' => 'Paul Durand', 'email' => 'paul.durand@example.com', 'password' => 'password123'],
            ['name' => 'Lucie Leroy', 'email' => 'lucie.leroy@example.com', 'password' => 'password123'],
            ['name' => 'Pierre Lefevre', 'email' => 'pierre.lefevre@example.com', 'password' => 'password123'],
            ['name' => 'Sophie Robert', 'email' => 'sophie.robert@example.com', 'password' => 'password123'],
            ['name' => 'Antoine Richard', 'email' => 'antoine.richard@example.com', 'password' => 'password123'],
            ['name' => 'Clara Dubois', 'email' => 'clara.dubois@example.com', 'password' => 'password123'],
            ['name' => 'David Moreau', 'email' => 'david.moreau@example.com', 'password' => 'password123'],
            ['name' => 'Isabelle Petit', 'email' => 'isabelle.petit@example.com', 'password' => 'password123'],
            ['name' => 'Thierry Faure', 'email' => 'thierry.faure@example.com', 'password' => 'password123'],
            ['name' => 'Emilie Boucher', 'email' => 'emilie.boucher@example.com', 'password' => 'password123'],
        ];

        foreach ($members as $member) {
            CompanyMember::create([
                'company_id' => $company->id,
                'name' => $member['name'],
                'email' => $member['email'],
                'password' => Hash::make($member['password'])
            ]);
        }
    }
}
