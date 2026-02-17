<?php

namespace App\Controller;

use App\Repository\ConsommationRepository;
use App\Repository\TypeEnergieRepository;
use App\Repository\AlerteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->redirectToRoute('login');
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(
        ConsommationRepository $consommationRepository,
        TypeEnergieRepository $typeEnergieRepository,
        AlerteRepository $alerteRepository
    ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $consommations = $consommationRepository->findBy(['user' => $user], ['dateReleve' => 'DESC']);
        $typesEnergie = $typeEnergieRepository->findAll();
        $alertesRecentes = $alerteRepository->findUnreadByUser($user, 5);

        $dailyCountMap = [];
        $typeCountMap = [];
        $typeColorMap = [];
        $defaultColors = ['#10B981', '#06B6D4', '#F59E0B', '#8B5CF6', '#F43F5E', '#0EA5E9'];
        $colorIndex = 0;

        foreach ($consommations as $consommation) {
            $dateReleve = $consommation->getDateReleve();
            if (null !== $dateReleve) {
                $dateKey = $dateReleve->format('Y-m-d');
                $dailyCountMap[$dateKey] = ($dailyCountMap[$dateKey] ?? 0) + 1;
            }

            $type = $consommation->getTypeEnergie();
            $typeLabel = $type?->getNom() ?? 'Non défini';
            $typeCountMap[$typeLabel] = ($typeCountMap[$typeLabel] ?? 0) + 1;
            if (!isset($typeColorMap[$typeLabel])) {
                $typeColorMap[$typeLabel] = $type?->getCouleur() ?? $defaultColors[$colorIndex % \count($defaultColors)];
                ++$colorIndex;
            }
        }

        ksort($dailyCountMap);
        arsort($typeCountMap);

        $chartData = [
            'daily' => [
                'labels' => array_map(
                    static fn (string $date): string => (new \DateTimeImmutable($date))->format('d/m'),
                    array_keys($dailyCountMap)
                ),
                'values' => array_values($dailyCountMap),
            ],
            'types' => [
                'labels' => array_keys($typeCountMap),
                'values' => array_values($typeCountMap),
                'colors' => array_map(
                    static fn (string $label): string => $typeColorMap[$label] ?? '#10B981',
                    array_keys($typeCountMap)
                ),
            ],
        ];

        return $this->render('dashboard/index.html.twig', [
            'consommations' => $consommations,
            'typesEnergie' => $typesEnergie,
            'alertesRecentes' => $alertesRecentes,
            'chartData' => $chartData,
        ]);
    }
}
