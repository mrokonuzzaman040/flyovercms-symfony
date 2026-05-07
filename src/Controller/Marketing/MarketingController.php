<?php

namespace App\Controller\Marketing;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketing')]
class MarketingController extends AbstractController
{
    #[Route('', name: 'marketing.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Index', [
            'campaigns' => [],
            'stats' => ['total' => 0, 'active' => 0, 'sent' => 0],
        ]);
    }

    #[Route('/campaigns', name: 'marketing.campaigns', methods: ['GET'])]
    public function campaigns(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Campaigns/Index', ['campaigns' => []]);
    }

    #[Route('/campaigns/sms', name: 'marketing.campaigns.sms', methods: ['GET'])]
    public function smsCampaigns(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Sms/Index', ['campaigns' => []]);
    }

    #[Route('/campaigns/email', name: 'marketing.campaigns.email', methods: ['GET'])]
    public function emailCampaigns(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Email/Index', ['campaigns' => []]);
    }

    #[Route('/campaigns/whatsapp', name: 'marketing.campaigns.whatsapp', methods: ['GET'])]
    public function whatsappCampaigns(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/WhatsApp/Index', ['campaigns' => []]);
    }

    #[Route('/campaigns/drip', name: 'marketing.campaigns.drip', methods: ['GET'])]
    public function dripCampaigns(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Drip/Index', ['campaigns' => []]);
    }

    #[Route('/templates', name: 'marketing.templates', methods: ['GET'])]
    public function templates(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Templates/Index', ['templates' => []]);
    }

    #[Route('/segments', name: 'marketing.segments', methods: ['GET'])]
    public function segments(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Segments/Index', ['segments' => []]);
    }

    #[Route('/analytics', name: 'marketing.analytics', methods: ['GET'])]
    public function analytics(InertiaInterface $inertia): Response
    {
        return $inertia->render('Marketing/Analytics/Index', ['analytics' => []]);
    }
}
