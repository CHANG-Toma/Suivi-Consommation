<?php

namespace App\Controller;

use App\Repository\ConsommationRepository;
use App\Repository\TypeEnergieRepository;
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
    public function dashboard(ConsommationRepository $consommationRepository, TypeEnergieRepository $typeEnergieRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $consommations = $consommationRepository->findBy(['user' => $user], ['dateReleve' => 'DESC']);
        $typesEnergie = $typeEnergieRepository->findAll();

        return $this->render('dashboard/index.html.twig', [
            'consommations' => $consommations,
            'typesEnergie' => $typesEnergie,
        ]);
    }
}
