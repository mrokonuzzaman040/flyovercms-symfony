<?php

namespace App\Controller\Auth;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PasswordResetController extends AbstractController
{
    #[Route('/forgot-password', name: 'password.request', methods: ['GET'])]
    public function request(InertiaInterface $inertia): Response
    {
        return $inertia->render('Auth/ForgotPassword', []);
    }

    #[Route('/forgot-password', name: 'password.email', methods: ['POST'])]
    public function sendResetLink(Request $request): Response
    {
        // TODO: implement password reset email via Symfony Mailer
        $this->addFlash('success', 'If that email exists, a reset link has been sent.');

        return $this->redirectToRoute('auth.login');
    }

    #[Route('/reset-password', name: 'password.update', methods: ['GET', 'POST'])]
    public function reset(Request $request, InertiaInterface $inertia): Response
    {
        if ($request->isMethod('GET')) {
            return $inertia->render('Auth/ResetPassword', [
                'token' => $request->query->get('token'),
                'email' => $request->query->get('email'),
            ]);
        }

        // TODO: validate token, update password
        $this->addFlash('success', 'Password updated. Please log in.');

        return $this->redirectToRoute('auth.login');
    }
}
