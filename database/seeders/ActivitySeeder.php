<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Activity::create([
            'message' => 'Bienvenue sur notre site',
            'intitule' => 'Introduction',
            'lien_hypertexte' => 'https://exemple.com/intro',
            'icone' => 'https://via.placeholder.com/200x200',
        ]);

        Activity::create([
            'message' => 'Nouveaux produits disponibles',
            'intitule' => 'Nouveautés',
            'lien_hypertexte' => 'https://exemple.com/nouveaux-produits',
            'icone' => 'https://via.placeholder.com/200x200',
        ]);

        Activity::create([
            'message' => 'Offre spéciale pour nos utilisateurs',
            'intitule' => 'Offres',
            'lien_hypertexte' => 'https://exemple.com/offres',
            'icone' => 'https://via.placeholder.com/200x200',
        ]);

        Activity::create([
            'message' => 'Découvrez nos dernières mises à jour',
            'intitule' => 'Mises à jour',
            'lien_hypertexte' => 'https://exemple.com/mises-a-jour',
        ]);
    }
}
