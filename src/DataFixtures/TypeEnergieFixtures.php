<?php

namespace App\DataFixtures;

use App\Entity\TypeEnergie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeEnergieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $typesEnergie = [
            [
                'nom' => 'Électricité',
                'unite' => 'kWh',
                'couleur' => '#F59E0B',
                'icone' => '⚡',
            ],
            [
                'nom' => 'Gaz',
                'unite' => 'm³',
                'couleur' => '#F97316',
                'icone' => '🔥',
            ],
            [
                'nom' => 'Eau',
                'unite' => 'm³',
                'couleur' => '#06B6D4',
                'icone' => '💧',
            ],
        ];

        foreach ($typesEnergie as $data) {
            $typeEnergie = new TypeEnergie();
            $typeEnergie->setNom($data['nom']);
            $typeEnergie->setUnite($data['unite']);
            $typeEnergie->setCouleur($data['couleur']);
            $typeEnergie->setIcone($data['icone']);

            $manager->persist($typeEnergie);
        }

        $manager->flush();
    }
}
