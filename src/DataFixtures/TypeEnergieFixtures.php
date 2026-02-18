<?php

namespace App\DataFixtures;

use App\Entity\TypeEnergie;
use App\Repository\TypeEnergieRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeEnergieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $repository = $manager->getRepository(TypeEnergie::class);

        $typesEnergie = [
            [
                'nom' => 'Électricité',
                'unite' => 'kWh',
                'couleur' => '#F59E0B',
                'icone' => '⚡',
            ],
            [
                'nom' => 'Gaz naturel',
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
            [
                'nom' => 'Fioul',
                'unite' => 'L',
                'couleur' => '#1F2937',
                'icone' => '🛢️',
            ],
            [
                'nom' => 'Bois',
                'unite' => 'stère',
                'couleur' => '#92400E',
                'icone' => '🪵',
            ],
            [
                'nom' => 'Pellets',
                'unite' => 'kg',
                'couleur' => '#78350F',
                'icone' => '🌲',
            ],
            [
                'nom' => 'Solaire',
                'unite' => 'kWh',
                'couleur' => '#FCD34D',
                'icone' => '☀️',
            ],
            [
                'nom' => 'Chauffage électrique',
                'unite' => 'kWh',
                'couleur' => '#EF4444',
                'icone' => '🔌',
            ],
        ];

        foreach ($typesEnergie as $data) {
            // Vérifier si le type d'énergie existe déjà
            $existing = $repository->findOneBy(['nom' => $data['nom']]);
            
            if (!$existing) {
                $typeEnergie = new TypeEnergie();
                $typeEnergie->setNom($data['nom']);
                $typeEnergie->setUnite($data['unite']);
                $typeEnergie->setCouleur($data['couleur']);
                $typeEnergie->setIcone($data['icone']);

                $manager->persist($typeEnergie);
            }
        }

        $manager->flush();
    }
}
