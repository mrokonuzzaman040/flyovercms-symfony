<?php

namespace App\Controller\Document;

use App\Entity\DocumentRequest;
use App\Entity\User;
use App\Repository\DocumentRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/document-requests')]
class DocumentRequestController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DocumentRequestRepository $repo,
    ) {}

    #[Route('', name: 'document-requests.index', methods: ['GET'])]
    public function index(Request $request, InertiaInterface $inertia): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $statusFilter = $request->query->get('status');
        $criteria = ['tenantId' => $user->getTenantId()];
        if ($statusFilter && $statusFilter !== 'all') {
            $criteria['status'] = $statusFilter;
        }

        $requests = $this->repo->findBy($criteria, ['createdAt' => 'DESC']);

        $grouped = [];
        foreach ($requests as $r) {
            $grouped[$r->getLeadId()][] = $this->serialize($r);
        }

        $stats = [
            'pending'   => count(array_filter($requests, fn(DocumentRequest $r) => $r->getStatus() === 'pending')),
            'fulfilled' => count(array_filter($requests, fn(DocumentRequest $r) => $r->getStatus() === 'fulfilled')),
            'cancelled' => count(array_filter($requests, fn(DocumentRequest $r) => $r->getStatus() === 'cancelled')),
            'total'     => count($requests),
        ];

        return $inertia->render('DocumentRequests/Index', [
            'groupedRequests' => array_values(array_map(
                fn(array $items) => ['lead_id' => $items[0]['lead_id'], 'requests' => $items],
                $grouped
            )),
            'stats'        => $stats,
            'statusFilter' => $statusFilter,
        ]);
    }

    #[Route('/{id}', name: 'document-requests.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        $docRequest = $this->findOr404($id);

        return $inertia->render('DocumentRequests/Show', [
            'documentRequest' => $this->serialize($docRequest),
        ]);
    }

    #[Route('/{id}/fulfill', name: 'document-requests.fulfill', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function fulfill(int $id, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $docRequest = $this->findOr404($id);
        $docRequest->setStatus('fulfilled');
        $docRequest->setFulfilledBy($user->getId());
        $docRequest->setFulfilledAt(new \DateTimeImmutable());
        $docRequest->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->redirectToRoute('document-requests.index');
    }

    #[Route('/{id}/cancel', name: 'document-requests.cancel', methods: ['POST', 'DELETE'], requirements: ['id' => '\d+'])]
    public function cancel(int $id): Response
    {
        $docRequest = $this->findOr404($id);
        $docRequest->setStatus('cancelled');
        $docRequest->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->redirectToRoute('document-requests.index');
    }

    private function findOr404(int $id): DocumentRequest
    {
        /** @var User $user */
        $user = $this->getUser();
        $r = $this->repo->findOneBy(['id' => $id, 'tenantId' => $user->getTenantId()]);
        if (!$r) throw $this->createNotFoundException();

        return $r;
    }

    private function serialize(DocumentRequest $r): array
    {
        return [
            'id'            => $r->getId(),
            'lead_id'       => $r->getLeadId(),
            'document_type' => $r->getDocumentType(),
            'message'       => $r->getMessage(),
            'status'        => $r->getStatus(),
            'requested_by'  => $r->getRequestedBy(),
            'fulfilled_by'  => $r->getFulfilledBy(),
            'fulfilled_at'  => $r->getFulfilledAt()?->format('Y-m-d H:i:s'),
            'created_at'    => $r->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
