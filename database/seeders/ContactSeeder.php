<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::first();
        $contacts = [
            ['fullname' => 'Alice Dupont', 'email' => 'alice.dupont@example.com', 'company_id' => $company->id],
            ['fullname' => 'Bob Martin', 'email' => 'bob.martin@example.com', 'company_id' => $company->id],
            ['fullname' => 'Claire Lefevre', 'email' => 'claire.lefevre@example.com', 'company_id' => $company->id],
            ['fullname' => 'David Bernard', 'email' => 'david.bernard@example.com', 'company_id' => $company->id],
            ['fullname' => 'Emma Roux', 'email' => 'emma.roux@example.com', 'company_id' => $company->id],
            ['fullname' => 'François Petit', 'email' => 'francois.petit@example.com', 'company_id' => $company->id],
            ['fullname' => 'Géraldine Caron', 'email' => 'geraldine.caron@example.com', 'company_id' => $company->id],
            ['fullname' => 'Hugo Leroy', 'email' => 'hugo.leroy@example.com', 'company_id' => $company->id],
            ['fullname' => 'Isabelle Moreau', 'email' => 'isabelle.moreau@example.com', 'company_id' => $company->id],
            ['fullname' => 'Julien Faure', 'email' => 'julien.faure@example.com', 'company_id' => $company->id],
            ['fullname' => 'Karine Lefevre', 'email' => 'karine.lefevre@example.com', 'company_id' => $company->id],
            ['fullname' => 'Louis Duchamp', 'email' => 'louis.duchamp@example.com', 'company_id' => $company->id],
            ['fullname' => 'Marie Clément', 'email' => 'marie.clement@example.com', 'company_id' => $company->id],
            ['fullname' => 'Nicolas Lefevre', 'email' => 'nicolas.lefevre@example.com', 'company_id' => $company->id],
            ['fullname' => 'Olivier Gauthier', 'email' => 'olivier.gauthier@example.com', 'company_id' => $company->id],
            ['fullname' => 'Paul Girard', 'email' => 'paul.girard@example.com', 'company_id' => $company->id],
            ['fullname' => 'Quentin Mercier', 'email' => 'quentin.mercier@example.com', 'company_id' => $company->id],
            ['fullname' => 'Roxane Dupuis', 'email' => 'roxane.dupuis@example.com', 'company_id' => $company->id],
            ['fullname' => 'Sophie Picard', 'email' => 'sophie.picard@example.com', 'company_id' => $company->id],
            ['fullname' => 'Thomas Dupuy', 'email' => 'thomas.dupuy@example.com', 'company_id' => $company->id],
        ];

        foreach ($contacts as $contact) {
            Contact::create($contact);
        }
    }
}
