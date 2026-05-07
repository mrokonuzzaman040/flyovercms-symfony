<?php

namespace App\Controller\Dashboard;

use App\Tenant\TenantContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Rompetomp\InertiaBundle\Service\InertiaInterface;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'dashboard.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia, TenantContext $tenantContext): Response
    {
        return $inertia->render('Dashboard/Index', [
            'tenant' => $tenantContext->get()->getName(),
        ]);
    }
}
