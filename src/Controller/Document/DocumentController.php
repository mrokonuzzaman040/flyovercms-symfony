<?php

namespace App\Controller\Document;

use App\Entity\Document;
use App\Entity\User;
use App\Repository\DocumentRepository;
use App\Repository\LeadRepository;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/documents')]
class DocumentController extends AbstractController
{
    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly LeadRepository $leadRepository,
        private readonly EntityManagerInterface $em,
        private readonly TenantContext $tenantContext,
    ) {}

    #[Route('', name: 'documents.index', methods: ['GET'])]
    public function index(Request $request, InertiaInterface $inertia): Response
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')->from(Document::class, 'd')
            ->orderBy('d.createdAt', 'DESC');

        if ($leadId = $request->query->get('lead_id')) {
            $qb->andWhere('d.leadId = :leadId')->setParameter('leadId', $leadId);
        }

        $documents = $qb->setMaxResults(50)->getQuery()->getResult();

        return $inertia->render('Documents/Index', [
            'documents' => array_map(fn(Document $d) => $this->serialize($d), $documents),
            'filters' => ['lead_id' => $request->query->get('lead_id')],
        ]);
    }

    #[Route('', name: 'documents.store', methods: ['POST'])]
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $file = $request->files->get('file');
        if (!$file) {
            $request->getSession()->getFlashBag()->add('error', 'No file provided.');
            return $this->redirectToRoute('documents.index');
        }

        $leadId = (int) $request->request->get('lead_id');
        $lead = $this->leadRepository->find($leadId);
        if (!$lead) {
            $request->getSession()->getFlashBag()->add('error', 'Lead not found.');
            return $this->redirectToRoute('documents.index');
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/documents';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = uniqid('doc_') . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);

        $doc = new Document();
        $doc->setTenantId($this->tenantContext->getId());
        $doc->setLeadId($leadId);
        $doc->setName($request->request->get('name') ?: $file->getClientOriginalName());
        $doc->setOriginalName($file->getClientOriginalName());
        $doc->setFilePath('uploads/documents/' . $filename);
        $doc->setFileType($file->getClientMimeType() ?? 'application/octet-stream');
        $doc->setFileSize($file->getSize());
        $doc->setDocumentType($request->request->get('document_type'));
        $doc->setDescription($request->request->get('description'));
        $doc->setUploadedBy($user->getId());

        $this->em->persist($doc);
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Document uploaded successfully.');

        return $this->redirectToRoute('leads.show', ['id' => $leadId]);
    }

    #[Route('/{id}', name: 'documents.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id, Request $request): RedirectResponse
    {
        $doc = $this->documentRepository->find($id);
        if (!$doc) throw $this->createNotFoundException();

        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $doc->getFilePath();
        if (file_exists($filePath)) unlink($filePath);

        $this->em->remove($doc);
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Document deleted.');

        return $this->redirectToRoute('documents.index');
    }

    private function serialize(Document $d): array
    {
        return [
            'id' => $d->getId(),
            'lead_id' => $d->getLeadId(),
            'name' => $d->getName(),
            'original_name' => $d->getOriginalName(),
            'file_path' => $d->getFilePath(),
            'file_type' => $d->getFileType(),
            'file_size' => $d->getFileSize(),
            'document_type' => $d->getDocumentType(),
            'description' => $d->getDescription(),
            'uploaded_by' => $d->getUploadedBy(),
            'created_at' => $d->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
