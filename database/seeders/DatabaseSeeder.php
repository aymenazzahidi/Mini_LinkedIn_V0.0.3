<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profil;
use App\Models\Competence;
use App\Models\Offre;
use App\Models\Candidature;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Compétences globales
        $competences = Competence::factory(10)->create();
        $niveaux = ['débutant', 'intermédiaire', 'expert'];

        // 2. Admins
        User::factory(2)->admin()->create();

        // 3. Recruteurs avec offres
        User::factory(5)->recruteur()->create()->each(function ($recruteur) {
            Offre::factory(rand(2, 3))->create(['user_id' => $recruteur->id]);
        });

        // 4. Candidats avec profil et compétences
        User::factory(10)->candidat()->create()->each(function ($candidat) use ($competences, $niveaux) {
            $profil = Profil::factory()->create(['user_id' => $candidat->id]);

            // Attacher 2 à 4 compétences aléatoires
            $selected = $competences->random(rand(2, 4));
            foreach ($selected as $competence) {
                $profil->competences()->attach($competence->id, [
                    'niveau' => $niveaux[array_rand($niveaux)],
                ]);
            }
        });

        // 5. Quelques candidatures
        $offres  = Offre::all();
        $profils = Profil::all();

        foreach ($profils->random(6) as $profil) {
            $offre = $offres->random();
            Candidature::firstOrCreate(
                ['offre_id' => $offre->id, 'profil_id' => $profil->id],
                ['message' => 'Je suis très motivé pour ce poste.', 'statut' => 'en_attente']
            );
        }
    }
}