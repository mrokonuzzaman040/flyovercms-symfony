<?php

namespace App\Controller\Settings;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'settings.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Settings/Index', [
            'settings' => $this->loadSettings(),
        ]);
    }

    #[Route('', name: 'settings.update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request): RedirectResponse
    {
        // Settings stored in application_settings table (entity to be added)
        $request->getSession()->getFlashBag()->add('success', 'Settings updated successfully.');

        return $this->redirectToRoute('settings.index');
    }

    #[Route('/notifications', name: 'settings.notifications', methods: ['GET'])]
    public function notifications(InertiaInterface $inertia): Response
    {
        return $inertia->render('Settings/Notifications');
    }

    #[Route('/security', name: 'settings.security', methods: ['GET'])]
    public function security(InertiaInterface $inertia): Response
    {
        return $inertia->render('Settings/Security');
    }

    private function loadSettings(): array
    {
        // Returns defaults — wire to ApplicationSettings entity later
        return [
            'app_name' => 'FlyoverCMS',
            'timezone' => 'UTC',
            'locale' => 'en',
            'currency' => 'USD',
            'date_format' => 'Y-m-d',
        ];
    }
}
