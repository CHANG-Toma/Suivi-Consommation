<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ProfileFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profil')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'profile_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }

        $profileForm = $this->createForm(ProfileFormType::class, $user, [
            'attr' => ['class' => 'space-y-1 text-gray-900 dark:text-gray-100'],
        ]);
        $passwordForm = $this->createForm(ChangePasswordFormType::class, null, [
            'attr' => ['class' => 'space-y-1 text-gray-900 dark:text-gray-100'],
        ]);

        $profileForm->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser instanceof User && $existingUser->getId() !== $user->getId()) {
                $profileForm->get('email')->addError(new FormError('Cette adresse email est déjà utilisée.'));
            } else {
                $entityManager->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès.');

                return $this->redirectToRoute('profile_index');
            }
        }

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $currentPassword = (string) $passwordForm->get('currentPassword')->getData();
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $passwordForm->get('currentPassword')->addError(new FormError('Mot de passe actuel incorrect.'));
            } else {
                $newPassword = (string) $passwordForm->get('plainPassword')->getData();
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $this->addFlash('success', 'Mot de passe mis à jour avec succès.');

                return $this->redirectToRoute('profile_index');
            }
        }

        return $this->render('profile/index.html.twig', [
            'profileForm' => $profileForm,
            'passwordForm' => $passwordForm,
            'user' => $user,
        ]);
    }
}
