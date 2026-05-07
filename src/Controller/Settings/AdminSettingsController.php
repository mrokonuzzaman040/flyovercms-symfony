<?php

namespace App\Controller\Settings;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/settings')]
class AdminSettingsController extends AbstractController
{
    #[Route('', name: 'admin.settings.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Settings/Admin/Index', [
            'settings' => [],
        ]);
    }

    #[Route('/email', name: 'admin.settings.email.update', methods: ['PUT', 'PATCH'])]
    public function updateEmail(Request $request): Response
    {
        // TODO: persist email settings
        $this->addFlash('success', 'Email settings updated.');

        return $this->redirectToRoute('admin.settings.index');
    }

    #[Route('/sms', name: 'admin.settings.sms.update', methods: ['PUT', 'PATCH'])]
    public function updateSms(Request $request): Response
    {
        // TODO: persist SMS settings
        $this->addFlash('success', 'SMS settings updated.');

        return $this->redirectToRoute('admin.settings.index');
    }

    #[Route('/whatsapp', name: 'admin.settings.whatsapp', methods: ['GET'])]
    public function whatsapp(InertiaInterface $inertia): Response
    {
        return $inertia->render('Settings/Admin/WhatsApp', ['settings' => []]);
    }

    #[Route('/whatsapp', name: 'admin.settings.whatsapp.update', methods: ['PUT', 'PATCH'])]
    public function updateWhatsapp(Request $request): Response
    {
        // TODO: persist WhatsApp settings
        $this->addFlash('success', 'WhatsApp settings updated.');

        return $this->redirectToRoute('admin.settings.index');
    }

    #[Route('/security', name: 'admin.settings.security.update', methods: ['PUT', 'PATCH'])]
    public function updateSecurity(Request $request): Response
    {
        // TODO: persist security settings
        $this->addFlash('success', 'Security settings updated.');

        return $this->redirectToRoute('admin.settings.index');
    }

    #[Route('/modules', name: 'admin.settings.modules.store', methods: ['POST'])]
    public function storeModule(Request $request): Response
    {
        // TODO: create module
        return $this->redirectToRoute('admin.settings.index');
    }

    #[Route('/modules/{id}/activate', name: 'admin.settings.modules.activate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function activateModule(int $id): Response
    {
        // TODO: activate module
        return $this->redirectToRoute('admin.settings.index');
    }

    #[Route('/modules/{id}/deactivate', name: 'admin.settings.modules.deactivate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deactivateModule(int $id): Response
    {
        // TODO: deactivate module
        return $this->redirectToRoute('admin.settings.index');
    }

    #[Route('/modules/{id}', name: 'admin.settings.modules.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroyModule(int $id): Response
    {
        // TODO: delete module
        return $this->redirectToRoute('admin.settings.index');
    }
}
