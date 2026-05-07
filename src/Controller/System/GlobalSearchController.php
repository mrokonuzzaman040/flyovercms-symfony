<?php

namespace App\Controller\System;

use App\Repository\LeadRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/search')]
class GlobalSearchController extends AbstractController
{
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly UserRepository $userRepository,
    ) {}

    #[Route('', name: 'search.global', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $term = trim($request->query->get('q', ''));

        if (strlen($term) < 2) {
            return new JsonResponse(['results' => []]);
        }

        $leads = $this->leadRepository->search($term);

        $results = array_map(fn($lead) => [
            'type' => 'lead',
            'id' => $lead->getId(),
            'title' => $lead->getFullName() ?? $lead->getPhone(),
            'subtitle' => $lead->getStatus(),
            'url' => '/leads/' . $lead->getId(),
        ], $leads);

        return new JsonResponse(['results' => $results]);
    }
}
