<?php

namespace App\Controller\Leads;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transfers')]
class TransferController extends AbstractController
{
    #[Route('/pending', name: 'transfers.pending', methods: ['GET'])]
    public function pending(InertiaInterface $inertia): Response
    {
        return $inertia->render('Leads/PendingTransfers', ['transfers' => []]);
    }

    #[Route('/{id}/accept', name: 'transfers.accept', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function accept(int $id): Response
    {
        // TODO: implement transfer accept logic
        return $this->redirectToRoute('transfers.pending');
    }

    #[Route('/accept-all', name: 'transfers.accept-all', methods: ['POST'])]
    public function acceptAll(): Response
    {
        // TODO: implement bulk accept
        return $this->redirectToRoute('transfers.pending');
    }

    #[Route('/{id}/reject', name: 'transfers.reject', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function reject(int $id): Response
    {
        // TODO: implement transfer reject logic
        return $this->redirectToRoute('transfers.pending');
    }

    #[Route('/reject-all', name: 'transfers.reject-all', methods: ['POST'])]
    public function rejectAll(): Response
    {
        // TODO: implement bulk reject
        return $this->redirectToRoute('transfers.pending');
    }

    #[Route('/{id}/cancel', name: 'transfers.cancel', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function cancel(int $id): Response
    {
        // TODO: implement cancel logic
        return $this->redirectToRoute('transfers.pending');
    }
}
