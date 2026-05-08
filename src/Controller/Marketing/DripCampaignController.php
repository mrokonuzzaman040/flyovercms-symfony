<?php

namespace App\Controller\Marketing;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketing/drip-campaigns')]
class DripCampaignController extends AbstractController
{
    #[Route('', name: 'marketing.drip-campaigns.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/DripCampaigns/Index', ['campaigns' => []]);
    }

    #[Route('/create', name: 'marketing.drip-campaigns.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/DripCampaigns/Create', []);
    }

    #[Route('', name: 'marketing.drip-campaigns.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        // TODO: persist drip campaign with steps
        return $this->redirectToRoute('marketing.drip-campaigns.index');
    }

    #[Route('/{id}', name: 'marketing.drip-campaigns.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/DripCampaigns/Show', ['campaign' => ['id' => $id]]);
    }

    #[Route('/{id}/edit', name: 'marketing.drip-campaigns.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/DripCampaigns/Edit', ['campaign' => ['id' => $id]]);
    }

    #[Route('/{id}', name: 'marketing.drip-campaigns.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        return $this->redirectToRoute('marketing.drip-campaigns.show', ['id' => $id]);
    }

    #[Route('/{id}', name: 'marketing.drip-campaigns.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): Response
    {
        return $this->redirectToRoute('marketing.drip-campaigns.index');
    }

    #[Route('/{id}/toggle-active', name: 'marketing.drip-campaigns.toggle-active', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleActive(int $id): JsonResponse
    {
        // TODO: toggle drip campaign active state
        return new JsonResponse(['success' => true]);
    }
}
