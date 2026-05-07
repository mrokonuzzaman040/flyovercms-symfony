<?php

namespace App\Controller\Users;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('', name: 'profile.show', methods: ['GET'])]
    public function show(InertiaInterface $inertia): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $inertia->render('Profile/Show', [
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar(),
                'phone' => $user->getPhone(),
                'timezone' => $user->getTimezone(),
                'language' => $user->getLanguage(),
            ],
        ]);
    }

    #[Route('', name: 'profile.update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = $request->request->all();

        $user->setName($data['name'] ?? $user->getName());
        $user->setPhone($data['phone'] ?? $user->getPhone());
        $user->setTimezone($data['timezone'] ?? $user->getTimezone());
        $user->setLanguage($data['language'] ?? $user->getLanguage());

        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Profile updated successfully.');

        return $this->redirectToRoute('profile.show');
    }

    #[Route('/password', name: 'profile.password.update', methods: ['PUT', 'PATCH'])]
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = $request->request->all();

        if (!$this->passwordHasher->isPasswordValid($user, $data['current_password'] ?? '')) {
            $request->getSession()->getFlashBag()->add('error', 'Current password is incorrect.');
            return $this->redirectToRoute('profile.show');
        }

        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $request->getSession()->getFlashBag()->add('error', 'New passwords do not match.');
            return $this->redirectToRoute('profile.show');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Password updated successfully.');

        return $this->redirectToRoute('profile.show');
    }
}
