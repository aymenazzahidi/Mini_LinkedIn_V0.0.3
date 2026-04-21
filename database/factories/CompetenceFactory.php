<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompetenceFactory extends Factory
{
    private static array $competences = [
        ['nom' => 'PHP',        'categorie' => 'Backend'],
        ['nom' => 'Laravel',    'categorie' => 'Backend'],
        ['nom' => 'JavaScript', 'categorie' => 'Frontend'],
        ['nom' => 'React',      'categorie' => 'Frontend'],
        ['nom' => 'Python',     'categorie' => 'Backend'],
        ['nom' => 'MySQL',      'categorie' => 'Base de données'],
        ['nom' => 'Docker',     'categorie' => 'DevOps'],
        ['nom' => 'Git',        'categorie' => 'Outils'],
        ['nom' => 'Vue.js',     'categorie' => 'Frontend'],
        ['nom' => 'Node.js',    'categorie' => 'Backend'],
    ];

    public function definition(): array
    {
        $item = fake()->unique()->randomElement(self::$competences);
        return [
            'nom'       => $item['nom'],
            'categorie' => $item['categorie'],
        ];
    }
}