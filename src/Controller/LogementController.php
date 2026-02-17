<?php

namespace App\Controller;

use App\Entity\Logement;
use App\Entity\User;
use App\Form\LogementType;
use App\Repository\LogementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/logements')]
class LogementController extends AbstractController
{
    #[Route('', name: 'logement_index', methods: ['GET'])]
    public function index(LogementRepository $logementRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }

        $logements = $logementRepository->findByUser($user);

        return $this->render('logement/index.html.twig', [
            'logements' => $logements,
        ]);
    }

    #[Route('/new', name: 'logement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }

        $logement = new Logement();
        $form = $this->createForm(LogementType::class, $logement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logement->setUser($user);

            $entityManager->persist($logement);
            $entityManager->flush();

            $this->addFlash('success', 'Le logement a bien été ajouté.');

            return $this->redirectToRoute('logement_index');
        }

        return $this->render('logement/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'logement_show', methods: ['GET'])]
    public function show(Logement $logement): Response
    {
        $this->denyUnlessOwner($logement);

        return $this->render('logement/show.html.twig', [
            'logement' => $logement,
        ]);
    }

    #[Route('/{id}/edit', name: 'logement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Logement $logement, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessOwner($logement);

        $form = $this->createForm(LogementType::class, $logement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le logement a bien été modifié.');

            return $this->redirectToRoute('logement_index');
        }

        return $this->render('logement/edit.html.twig', [
            'logement' => $logement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'logement_delete', methods: ['POST'])]
    public function delete(Request $request, Logement $logement, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessOwner($logement);

        if ($this->isCsrfTokenValid('delete_logement_'.$logement->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($logement);
            $entityManager->flush();
            $this->addFlash('success', 'Le logement a bien été supprimé.');
        } else {
            $this->addFlash('error', 'Action invalide, veuillez réessayer.');
        }

        return $this->redirectToRoute('logement_index');
    }

    private function denyUnlessOwner(Logement $logement): void
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($logement->getUser()?->getId() !== $user->getId()) {
            throw $this->createNotFoundException();
        }
    }
}
