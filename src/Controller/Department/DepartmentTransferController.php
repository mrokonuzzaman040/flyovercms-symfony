<?php

namespace App\Controller\Department;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/departments/transfers')]
class DepartmentTransferController extends AbstractController
{
    #[Route('/pending', name: 'departments.transfers.pending', methods: ['GET'])]
    public function pending(InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Transfers/Pending', ['transfers' => []]);
    }

    #[Route('/{id}', name: 'departments.transfers.show-form', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showForm(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Transfers/Form', ['transfer' => ['id' => $id]]);
    }

    #[Route('', name: 'departments.transfers.transfer', methods: ['POST'])]
    public function transfer(Request $request): JsonResponse
    {
        // TODO: initiate department transfer
        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/accept', name: 'departments.transfers.accept', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function accept(int $id): JsonResponse
    {
        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/reject', name: 'departments.transfers.reject', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function reject(int $id, Request $request): JsonResponse
    {
        return new JsonResponse(['success' => true]);
    }
}
