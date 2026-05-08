<?php

namespace App\Controller\Marketing;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Standard marketing campaigns CRUD + campaign lifecycle actions
 */
#[Route('/marketing/campaigns')]
class MarketingCampaignController extends AbstractController
{
    #[Route('', name: 'marketing.campaigns.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Campaigns/Index', ['campaigns' => []]);
    }

    #[Route('/create', name: 'marketing.campaigns.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Campaigns/Create', []);
    }

    #[Route('', name: 'marketing.campaigns.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        // TODO: persist campaign
        return $this->redirectToRoute('marketing.campaigns.index');
    }

    #[Route('/{id}', name: 'marketing.campaigns.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Campaigns/Show', ['campaign' => ['id' => $id]]);
    }

    #[Route('/{id}/edit', name: 'marketing.campaigns.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Campaigns/Edit', ['campaign' => ['id' => $id]]);
    }

    #[Route('/{id}', name: 'marketing.campaigns.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        return $this->redirectToRoute('marketing.campaigns.show', ['id' => $id]);
    }

    #[Route('/{id}', name: 'marketing.campaigns.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): Response
    {
        return $this->redirectToRoute('marketing.campaigns.index');
    }

    // ── Lifecycle actions ─────────────────────────────────────────────────────

    #[Route('/{id}/start', name: 'marketing.campaigns.start', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function start(int $id): JsonResponse
    {
        // TODO: start campaign
        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/cancel', name: 'marketing.campaigns.cancel', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function cancel(int $id): JsonResponse
    {
        // TODO: cancel campaign
        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/resume', name: 'marketing.campaigns.resume', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function resume(int $id): JsonResponse
    {
        // TODO: resume paused campaign
        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/leads', name: 'marketing.campaigns.leads', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function leads(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Campaigns/Leads', ['campaign' => ['id' => $id], 'leads' => []]);
    }

    #[Route('/{id}/prepare-recipients', name: 'marketing.campaigns.prepare-recipients', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function prepareRecipients(int $id, Request $request): JsonResponse
    {
        // TODO: prepare recipient list for campaign
        return new JsonResponse(['success' => true, 'count' => 0]);
    }
}
