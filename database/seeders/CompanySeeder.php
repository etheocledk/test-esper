<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Notification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       Company::create([
            'logo' => 'https://picsum.photos/300',
            'name' => 'TechCorp',
            'email' => 'contact@techcorp.com',
            'abonnement' => 'premium',
            'amount' => 1000,
            'password' => Hash::make('Password@123'),
            'bio' => 'A tech company specializing in software development.',
            'code_postal' => '75001',
            'phone' => '+33123456789',
            'address' => '123 Tech Street, Paris, France',
            'numerofiscal' => 'FR123456789'
        ]);

        Company::create([
            'logo' => 'https://picsum.photos/300',
            'name' => 'Creative Media',
            'email' => 'info@creativemedia.com',
            'abonnement' => 'basic',
            'amount' => 500,
            'password' => Hash::make('Password@123'),
            'bio' => 'A creative agency providing marketing services.',
            'code_postal' => '69001',
            'phone' => '+33456789012',
            'address' => '456 Media Avenue, Lyon, France',
            'numerofiscal' => 'FR987654321'
        ]);

        Company::create([
            'logo' => 'https://picsum.photos/300',
            'name' => 'GreenEnergy Solutions',
            'email' => 'contact@greenenergy.com',
            'abonnement' => 'enterprise',
            'amount' => 5000,
            'password' => Hash::make('Password@123'),
            'bio' => 'A leading provider of sustainable energy solutions.',
            'code_postal' => '33000',
            'phone' => '+33567890123',
            'address' => '789 Energy Road, Bordeaux, France',
            'numerofiscal' => 'FR112233445'
        ]);
    }
}
