<?php

namespace App\Controller\Marketing;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketing/automated-campaigns')]
class AutomatedCampaignController extends AbstractController
{
    #[Route('', name: 'marketing.automated-campaigns.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/AutomatedCampaigns/Index', ['campaigns' => []]);
    }

    #[Route('/create', name: 'marketing.automated-campaigns.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/AutomatedCampaigns/Create', []);
    }

    #[Route('', name: 'marketing.automated-campaigns.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        // TODO: persist automated campaign
        return $this->redirectToRoute('marketing.automated-campaigns.index');
    }

    #[Route('/{id}', name: 'marketing.automated-campaigns.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/AutomatedCampaigns/Show', ['campaign' => ['id' => $id]]);
    }

    #[Route('/{id}/edit', name: 'marketing.automated-campaigns.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/AutomatedCampaigns/Edit', ['campaign' => ['id' => $id]]);
    }

    #[Route('/{id}', name: 'marketing.automated-campaigns.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        return $this->redirectToRoute('marketing.automated-campaigns.show', ['id' => $id]);
    }

    #[Route('/{id}', name: 'marketing.automated-campaigns.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): Response
    {
        return $this->redirectToRoute('marketing.automated-campaigns.index');
    }

    #[Route('/{id}/toggle-active', name: 'marketing.automated-campaigns.toggle-active', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleActive(int $id): JsonResponse
    {
        // TODO: toggle campaign active state
        return new JsonResponse(['success' => true]);
    }
}
