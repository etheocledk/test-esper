<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run()
    {
        Faq::create([
            'titre' => 'Qu\'est-ce que Laravel ?',
            'reponse' => 'Laravel est un framework PHP open-source pour le développement d\'applications web.',
            'categorie' => 'Général',
            'icone' => 'https://picsum.photos/300',
        ]);

        Faq::create([
            'titre' => 'Comment installer Laravel ?',
            'reponse' => 'Vous pouvez installer Laravel en utilisant Composer : composer create-project --prefer-dist laravel/laravel nom-du-projet.',
            'categorie' => 'Installation',
            'icone' => 'https://picsum.photos/300',
        ]);

        Faq::create([
            'titre' => 'Laravel supporte-t-il l\'authentification ?',
            'reponse' => 'Oui, Laravel dispose d\'un système d\'authentification intégré pour gérer les utilisateurs.',
            'categorie' => 'Authentification',
        ]);
    }
}
