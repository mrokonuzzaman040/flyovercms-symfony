<?php

namespace App\Controller\Marketing;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketing/multi-channel-campaigns')]
class MultiChannelCampaignController extends AbstractController
{
    #[Route('', name: 'marketing.multi-channel-campaigns.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/MultiChannelCampaigns/Index', ['campaigns' => []]);
    }

    #[Route('/create', name: 'marketing.multi-channel-campaigns.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/MultiChannelCampaigns/Create', []);
    }

    #[Route('', name: 'marketing.multi-channel-campaigns.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        // TODO: persist multi-channel campaign
        return $this->redirectToRoute('marketing.multi-channel-campaigns.index');
    }

    #[Route('/{id}', name: 'marketing.multi-channel-campaigns.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/MultiChannelCampaigns/Show', ['campaign' => ['id' => $id]]);
    }

    #[Route('/{id}/edit', name: 'marketing.multi-channel-campaigns.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/MultiChannelCampaigns/Edit', ['campaign' => ['id' => $id]]);
    }

    #[Route('/{id}', name: 'marketing.multi-channel-campaigns.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        return $this->redirectToRoute('marketing.multi-channel-campaigns.show', ['id' => $id]);
    }

    #[Route('/{id}', name: 'marketing.multi-channel-campaigns.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): Response
    {
        return $this->redirectToRoute('marketing.multi-channel-campaigns.index');
    }
}
