<?php

namespace App\Controller\System;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/office-visits')]
class OfficeVisitController extends AbstractController
{
    #[Route('/{id}/complete', name: 'office-visits.complete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function complete(int $id, Request $request): JsonResponse
    {
        // TODO: implement office visit completion logic
        return new JsonResponse(['success' => true]);
    }
}
