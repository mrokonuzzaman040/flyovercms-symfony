<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Rompetomp\InertiaBundle\Service\InertiaInterface;

class AuthController extends AbstractController
{
    #[Route('/', name: 'homepage', methods: ['GET'])]
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard.index');
        }

        return $this->redirectToRoute('auth.login');
    }

    #[Route('/login', name: 'auth.login', methods: ['GET', 'POST'])]
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(InertiaInterface $inertia, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard.index');
        }

        return $inertia->render('Auth/Login', [
            'errors' => $authenticationUtils->getLastAuthenticationError()?->getMessageKey()
                ? ['email' => 'These credentials do not match our records.']
                : (object) [],
            'lastEmail' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/logout', name: 'auth.logout', methods: ['GET'])]
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): never
    {
        // Handled by Symfony Security — this method never executes
        throw new \LogicException('Logout is handled by the security firewall.');
    }
}
