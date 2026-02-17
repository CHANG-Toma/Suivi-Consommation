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
        $monthStart = new \DateTimeImmutable('first day of this month 00:00:00');
        $nextMonthStart = $monthStart->modify('+1 month');
        $monthlyTotalsByType = $consommationRepository->getMonthlyTotalsByType($user, $monthStart, $nextMonthStart);
        $daysInMonth = (int) $monthStart->format('t');
        $dayLabels = array_map(static fn (int $day): string => str_pad((string) $day, 2, '0', STR_PAD_LEFT), range(1, $daysInMonth));

        $perTypeChartData = [];
        foreach ($typesEnergie as $typeEnergie) {
            $typeId = $typeEnergie->getId();
            if (null === $typeId) {
                continue;
            }

            $perTypeChartData[(string) $typeId] = [
                'labels' => $dayLabels,
                'values' => array_fill(0, $daysInMonth, 0.0),
                'color' => $typeEnergie->getCouleur() ?? '#10B981',
            ];
        }

        foreach ($consommations as $consommation) {
            $dateReleve = $consommation->getDateReleve();
            $typeEnergie = $consommation->getTypeEnergie();

            if (null === $dateReleve || null === $typeEnergie || null === $typeEnergie->getId()) {
                continue;
            }

            if ($dateReleve < $monthStart || $dateReleve >= $nextMonthStart) {
                continue;
            }

            $dayIndex = (int) $dateReleve->format('j') - 1;
            if ($dayIndex < 0 || $dayIndex >= $daysInMonth) {
                continue;
            }

            $valeur = (float) ($consommation->getValeur() ?? 0);
            $typeId = (string) $typeEnergie->getId();
            if (isset($perTypeChartData[$typeId])) {
                $perTypeChartData[$typeId]['values'][$dayIndex] += $valeur;
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'consommations' => $consommations,
            'typesEnergie' => $typesEnergie,
            'alertesRecentes' => $alertesRecentes,
            'monthlyTotalsByType' => $monthlyTotalsByType,
            'currentMonthLabel' => $monthStart->format('m/Y'),
            'perTypeChartData' => $perTypeChartData,
        ]);
    }
}
