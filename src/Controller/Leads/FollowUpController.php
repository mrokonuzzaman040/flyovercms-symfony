<?php

namespace App\Controller\Leads;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/follow-ups')]
class FollowUpController extends AbstractController
{
    #[Route('/history', name: 'follow-ups.history', methods: ['GET'])]
    public function history(InertiaInterface $inertia): Response
    {
        return $inertia->render('FollowUps/History', ['followUps' => []]);
    }

    #[Route('/{id}/complete', name: 'follow-ups.complete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function complete(int $id): Response
    {
        // TODO: mark follow-up complete
        return $this->redirectToRoute('follow-ups.history');
    }

    #[Route('/{id}/reschedule', name: 'follow-ups.reschedule', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function reschedule(int $id): Response
    {
        // TODO: reschedule follow-up
        return $this->redirectToRoute('follow-ups.history');
    }

    #[Route('/{id}', name: 'follow-ups.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id): Response
    {
        // TODO: update follow-up
        return $this->redirectToRoute('follow-ups.history');
    }
}
