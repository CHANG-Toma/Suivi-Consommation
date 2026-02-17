<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Entity\Consommation;
use App\Entity\User;
use App\Form\ConsommationType;
use App\Repository\AlerteRepository;
use App\Repository\ConsommationRepository;
use App\Repository\LogementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consommations')]
class ConsommationController extends AbstractController
{
    #[Route('', name: 'consommation_index', methods: ['GET'])]
    public function index(ConsommationRepository $consommationRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }

        return $this->render('consommation/index.html.twig', [
            'consommations' => $consommationRepository->findByUser($user),
        ]);
    }

    #[Route('/new', name: 'consommation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        LogementRepository $logementRepository,
        AlerteRepository $alerteRepository
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }

        if (\count($logementRepository->findByUser($user)) === 0) {
            $this->addFlash('error', 'Ajoutez d\'abord un logement avant de saisir une consommation.');

            return $this->redirectToRoute('logement_new');
        }

        $consommation = new Consommation();
        $form = $this->createForm(ConsommationType::class, $consommation, [
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $consommation->setUser($user);
            $entityManager->persist($consommation);
            $entityManager->flush();
            $this->handleTriggeredAlerts($consommation, $alerteRepository, $entityManager);

            $this->addFlash('success', 'La consommation a bien été enregistrée.');

            return $this->redirectToRoute('consommation_index');
        }

        return $this->render('consommation/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'consommation_show', methods: ['GET'])]
    public function show(Consommation $consommation): Response
    {
        $this->denyUnlessOwner($consommation);

        return $this->render('consommation/show.html.twig', [
            'consommation' => $consommation,
        ]);
    }

    #[Route('/{id}/edit', name: 'consommation_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Consommation $consommation,
        EntityManagerInterface $entityManager,
        AlerteRepository $alerteRepository
    ): Response {
        $this->denyUnlessOwner($consommation);

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ConsommationType::class, $consommation, [
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->handleTriggeredAlerts($consommation, $alerteRepository, $entityManager);
            $this->addFlash('success', 'La consommation a bien été modifiée.');

            return $this->redirectToRoute('consommation_index');
        }

        return $this->render('consommation/edit.html.twig', [
            'consommation' => $consommation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'consommation_delete', methods: ['POST'])]
    public function delete(Request $request, Consommation $consommation, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessOwner($consommation);

        if ($this->isCsrfTokenValid('delete_consommation_'.$consommation->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($consommation);
            $entityManager->flush();
            $this->addFlash('success', 'La consommation a bien été supprimée.');
        } else {
            $this->addFlash('error', 'Action invalide, veuillez réessayer.');
        }

        return $this->redirectToRoute('consommation_index');
    }

    private function denyUnlessOwner(Consommation $consommation): void
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($consommation->getUser()?->getId() !== $user->getId()) {
            throw $this->createNotFoundException();
        }
    }

    private function handleTriggeredAlerts(
        Consommation $consommation,
        AlerteRepository $alerteRepository,
        EntityManagerInterface $entityManager
    ): void {
        $user = $consommation->getUser();
        $logement = $consommation->getLogement();
        if (!$user instanceof User || null === $logement || null === $consommation->getValeur()) {
            return;
        }

        $triggeredAlerts = $alerteRepository->findTriggeredByConsumption(
            $user,
            $logement,
            $consommation->getTypeEnergie(),
            (float) $consommation->getValeur()
        );

        if (\count($triggeredAlerts) === 0) {
            return;
        }

        $hasChanges = false;
        foreach ($triggeredAlerts as $alert) {
            if (!$alert instanceof Alerte) {
                continue;
            }

            if ($alert->isLu()) {
                $alert->setLu(false);
                $hasChanges = true;
            }

            $labelType = $alert->getTypeEnergie()?->getNom() ?? 'Tous types';
            $this->addFlash(
                'warning_popup',
                sprintf(
                    'Alerte "%s" déclenchée: %s a atteint %.2f%s (seuil: %s).',
                    $alert->getTitre(),
                    $labelType,
                    (float) $consommation->getValeur(),
                    $consommation->getTypeEnergie()?->getUnite() ? ' '.$consommation->getTypeEnergie()->getUnite() : '',
                    $alert->getSeuil()
                )
            );
        }

        if ($hasChanges) {
            $entityManager->flush();
        }
    }
}
