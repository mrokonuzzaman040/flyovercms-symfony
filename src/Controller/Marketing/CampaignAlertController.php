<?php

namespace App\Controller\Marketing;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketing/campaign-alerts')]
class CampaignAlertController extends AbstractController
{
    #[Route('', name: 'marketing.campaign-alerts.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/CampaignAlerts/Index', ['alerts' => []]);
    }

    #[Route('/create', name: 'marketing.campaign-alerts.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/CampaignAlerts/Create', []);
    }

    #[Route('', name: 'marketing.campaign-alerts.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        // TODO: persist campaign alert
        return $this->redirectToRoute('marketing.campaign-alerts.index');
    }

    #[Route('/{id}', name: 'marketing.campaign-alerts.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/CampaignAlerts/Show', ['alert' => ['id' => $id]]);
    }

    #[Route('/{id}/edit', name: 'marketing.campaign-alerts.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/CampaignAlerts/Edit', ['alert' => ['id' => $id]]);
    }

    #[Route('/{id}', name: 'marketing.campaign-alerts.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        return $this->redirectToRoute('marketing.campaign-alerts.show', ['id' => $id]);
    }

    #[Route('/{id}', name: 'marketing.campaign-alerts.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): Response
    {
        return $this->redirectToRoute('marketing.campaign-alerts.index');
    }

    #[Route('/{id}/acknowledge', name: 'marketing.campaign-alerts.acknowledge', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function acknowledge(int $id): JsonResponse
    {
        // TODO: mark alert as acknowledged
        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/reset', name: 'marketing.campaign-alerts.reset', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function reset(int $id): JsonResponse
    {
        // TODO: reset alert trigger state
        return new JsonResponse(['success' => true]);
    }
}
