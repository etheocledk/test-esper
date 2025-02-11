<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Project::create([
            'title' => 'Projet A',
            'theme' => 'Environnement',
            'association_name' => 'Green Earth',
            'city' => 'Paris',
            'events_offered' => 'Collecte de déchets et sensibilisation.',
            'iban' => 'FR7630006000011234567890189',
            'description' => 'Un projet visant à nettoyer les plages et à éduquer les jeunes générations.',
            'image' => 'https://picsum.photos/200',
            'video' => 'https://www.example.com/video1',
            'fiscal_receipt' => 'FiscalRec1',
        ]);

        Project::create([
            'title' => 'Projet B',
            'theme' => 'Éducation',
            'association_name' => 'Bright Minds',
            'city' => 'Lyon',
            'events_offered' => 'Ateliers de soutien scolaire.',
            'iban' => 'FR7630006000022345678901234',
            'description' => 'Propose des ateliers de soutien pour les enfants des quartiers sensibles.',
            'image' => 'https://picsum.photos/200',
            'video' => 'https://www.example.com/video2',
            'fiscal_receipt' => 'FiscalRec2',
        ]);

        Project::create([
            'title' => 'Projet C',
            'theme' => 'Santé',
            'association_name' => 'Health for All',
            'city' => 'Marseille',
            'events_offered' => 'Campagne de vaccination gratuite.',
            'iban' => 'FR7630006000033456789012345',
            'description' => 'Nous organisons des journées de vaccination dans les quartiers populaires.',
            'image' => 'https://picsum.photos/200',
            'video' => 'https://www.example.com/video3',
            'fiscal_receipt' => 'FiscalRec3',
        ]);

        Project::create([
            'title' => 'Projet D',
            'theme' => 'Inclusion',
            'association_name' => 'Unity Together',
            'city' => 'Bordeaux',
            'events_offered' => 'Séances de formation pour les personnes handicapées.',
            'iban' => 'FR7630006000044567890123456',
            'description' => 'Des sessions de formation pour aider les personnes en situation de handicap à s’intégrer professionnellement.',
            'image' => 'https://picsum.photos/200',
            'video' => 'https://www.example.com/video4',
            'fiscal_receipt' => 'FiscalRec4',
        ]);

        Project::create([
            'title' => 'Projet E',
            'theme' => 'Culture',
            'association_name' => 'Artistic Souls',
            'city' => 'Toulouse',
            'events_offered' => 'Exposition d’art et concerts gratuits.',
            'iban' => 'FR7630006000055678901234567',
            'description' => 'Organiser des événements culturels pour promouvoir l’art local.',
            'image' => 'https://picsum.photos/200',
            'video' => 'https://www.example.com/video5',
            'fiscal_receipt' => 'FiscalRec5',
        ]);

        Project::create([
            'title' => 'Projet F',
            'theme' => 'Technologie',
            'association_name' => 'Tech Innovators',
            'city' => 'Nice',
            'events_offered' => 'Hackathon et développement d’applications.',
            'iban' => 'FR7630006000066789012345678',
            'description' => 'Un hackathon pour développer des solutions numériques pour l’éducation.',
            'image' => 'https://picsum.photos/200',
            'video' => 'https://www.example.com/video6',
            'fiscal_receipt' => 'FiscalRec6',
        ]);

        Project::create([
            'title' => 'Projet G',
            'theme' => 'Sport',
            'association_name' => 'Active Youth',
            'city' => 'Lille',
            'events_offered' => 'Compétitions sportives pour jeunes.',
            'iban' => 'FR7630006000077890123456789',
            'description' => 'Organiser des événements sportifs pour encourager les jeunes à pratiquer des activités physiques.',
            'image' => 'https://picsum.photos/200',
            'video' => 'https://www.example.com/video7',
            'fiscal_receipt' => 'FiscalRec7',
        ]);

        Project::create([
            'title' => 'Projet H',
            'theme' => 'Solidarité',
            'association_name' => 'Helping Hands',
            'city' => 'Rennes',
            'events_offered' => 'Distribution de nourriture aux sans-abris.',
            'iban' => 'FR7630006000088901234567890',
            'description' => 'Distribution hebdomadaire de repas chauds aux personnes sans-abri.',
            'image' => 'https://picsum.photos/200',
            'video' => 'https://www.example.com/video8',
            'fiscal_receipt' => 'FiscalRec8',
        ]);

        Project::create([
            'title' => 'Projet I',
            'theme' => 'Environnement',
            'association_name' => 'EcoFuture',
            'city' => 'Strasbourg',
            'events_offered' => 'Journées de plantation d’arbres.',
            'iban' => 'FR7630006000099012345678901',
            'description' => 'Participer à la plantation d’arbres pour restaurer les espaces verts en ville.',
            'image' => 'https://picsum.photos/200',
            'video' => 'https://www.example.com/video9',
            'fiscal_receipt' => 'FiscalRec9',
        ]);

        Project::create([
            'title' => 'Projet J',
            'theme' => 'Éducation',
            'association_name' => 'Learn Together',
            'city' => 'Nantes',
            'events_offered' => 'Cours de mathématiques pour collégiens.',
            'iban' => 'FR7630006000010123456789012',
            'description' => 'Cours gratuits de mathématiques pour les élèves en difficulté.',
            'image' => 'https://picsum.photos/300',
            'video' => 'https://www.example.com/video10',
            'fiscal_receipt' => 'FiscalRec10',
        ]);
    }
}
