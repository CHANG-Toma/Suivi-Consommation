<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Entity\User;
use App\Form\AlerteType;
use App\Repository\AlerteRepository;
use App\Repository\LogementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/alertes')]
class AlerteController extends AbstractController
{
    #[Route('', name: 'alerte_index', methods: ['GET'])]
    public function index(AlerteRepository $alerteRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }

        return $this->render('alerte/index.html.twig', [
            'alertes' => $alerteRepository->findByUser($user),
        ]);
    }

    #[Route('/new', name: 'alerte_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LogementRepository $logementRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }

        if (\count($logementRepository->findByUser($user)) === 0) {
            $this->addFlash('error', 'Ajoutez d\'abord un logement avant de créer une alerte.');

            return $this->redirectToRoute('logement_new');
        }

        $alerte = new Alerte();
        $form = $this->createForm(AlerteType::class, $alerte, [
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $alerte->setUser($user);
            $entityManager->persist($alerte);
            $entityManager->flush();

            $this->addFlash('success', 'L\'alerte a bien été créée.');

            return $this->redirectToRoute('alerte_index');
        }

        return $this->render('alerte/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'alerte_show', methods: ['GET'])]
    public function show(Alerte $alerte, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessOwner($alerte);

        if (!$alerte->isLu()) {
            $alerte->setLu(true);
            $entityManager->flush();
        }

        return $this->render('alerte/show.html.twig', [
            'alerte' => $alerte,
        ]);
    }

    #[Route('/{id}/edit', name: 'alerte_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Alerte $alerte, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessOwner($alerte);

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AlerteType::class, $alerte, [
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'L\'alerte a bien été modifiée.');

            return $this->redirectToRoute('alerte_index');
        }

        return $this->render('alerte/edit.html.twig', [
            'alerte' => $alerte,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle-read', name: 'alerte_toggle_read', methods: ['POST'])]
    public function toggleRead(Request $request, Alerte $alerte, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessOwner($alerte);

        if ($this->isCsrfTokenValid('toggle_alerte_'.$alerte->getId(), (string) $request->request->get('_token'))) {
            $alerte->setLu(!$alerte->isLu());
            $entityManager->flush();
            $this->addFlash('success', $alerte->isLu() ? 'Alerte marquée comme lue.' : 'Alerte marquée comme non lue.');
        } else {
            $this->addFlash('error', 'Action invalide, veuillez réessayer.');
        }

        return $this->redirectToRoute('alerte_index');
    }

    #[Route('/{id}', name: 'alerte_delete', methods: ['POST'])]
    public function delete(Request $request, Alerte $alerte, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessOwner($alerte);

        if ($this->isCsrfTokenValid('delete_alerte_'.$alerte->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($alerte);
            $entityManager->flush();
            $this->addFlash('success', 'L\'alerte a bien été supprimée.');
        } else {
            $this->addFlash('error', 'Action invalide, veuillez réessayer.');
        }

        return $this->redirectToRoute('alerte_index');
    }

    private function denyUnlessOwner(Alerte $alerte): void
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($alerte->getUser()?->getId() !== $user->getId()) {
            throw $this->createNotFoundException();
        }
    }
}
