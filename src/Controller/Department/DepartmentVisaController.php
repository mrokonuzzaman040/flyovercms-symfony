<?php

namespace App\Controller\Department;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/departments/visa')]
class DepartmentVisaController extends AbstractController
{
    #[Route('', name: 'departments.visa.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Visa/Index', ['leads' => []]);
    }

    #[Route('/{id}', name: 'departments.visa.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Visa/Show', ['lead' => ['id' => $id]]);
    }

    #[Route('/leads', name: 'departments.visa.leads', methods: ['GET'])]
    public function leads(InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Visa/Leads', ['leads' => []]);
    }

    #[Route('/analytics', name: 'departments.visa.analytics', methods: ['GET'])]
    public function analytics(InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Visa/Analytics', ['analytics' => []]);
    }

    #[Route('/{id}/documents', name: 'departments.visa.documents', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function documents(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Visa/Documents', ['lead' => ['id' => $id], 'documents' => []]);
    }

    #[Route('/{id}/request-document', name: 'departments.visa.request-document', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function requestDocument(int $id, Request $request): JsonResponse
    {
        // TODO: create document request
        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/documents/download', name: 'departments.visa.download-documents', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function downloadDocuments(int $id): Response
    {
        // TODO: zip and stream documents
        return new Response('Download placeholder', 200, ['Content-Type' => 'application/zip']);
    }

    #[Route('/{id}/export-pdf', name: 'departments.visa.export-pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function exportPdf(int $id): Response
    {
        // TODO: generate PDF
        return new Response('PDF placeholder', 200, ['Content-Type' => 'application/pdf']);
    }

    #[Route('/exports', name: 'departments.visa.exports', methods: ['GET'])]
    public function exports(InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Visa/Exports', ['exports' => []]);
    }

    #[Route('/{id}/status', name: 'departments.visa.update-status', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        // TODO: update lead visa status
        return new JsonResponse(['success' => true]);
    }
}
