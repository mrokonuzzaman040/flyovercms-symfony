<?php

namespace App\Controller\Document;

use App\Entity\Document;
use App\Entity\User;
use App\Repository\DocumentRepository;
use App\Repository\LeadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/leads/{lead_id}/documents', requirements: ['lead_id' => '\d+'])]
class LeadDocumentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DocumentRepository $docRepo,
        private readonly LeadRepository $leadRepo,
    ) {}

    #[Route('', name: 'leads.documents.index', methods: ['GET'])]
    public function index(int $lead_id, InertiaInterface $inertia): Response
    {
        $this->findLeadOr404($lead_id);
        $docs = $this->docRepo->findBy(['leadId' => $lead_id], ['createdAt' => 'DESC']);

        return $inertia->render('Leads/Documents/Index', [
            'lead_id'   => $lead_id,
            'documents' => array_map(fn(Document $d) => $this->serialize($d), $docs),
        ]);
    }

    #[Route('/create', name: 'leads.documents.create', methods: ['GET'])]
    public function create(int $lead_id, InertiaInterface $inertia): Response
    {
        $this->findLeadOr404($lead_id);

        return $inertia->render('Leads/Documents/Create', ['lead_id' => $lead_id]);
    }

    #[Route('', name: 'leads.documents.store', methods: ['POST'])]
    public function store(int $lead_id, Request $request): Response
    {
        $this->findLeadOr404($lead_id);
        /** @var User $user */
        $user = $this->getUser();

        $file = $request->files->get('file');
        if (!$file) {
            return $this->redirectToRoute('leads.documents.index', ['lead_id' => $lead_id]);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/documents';
        $filename  = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);

        $doc = new Document();
        $doc->setLeadId($lead_id);
        $doc->setUploadedBy($user->getId());
        $doc->setFileName($file->getClientOriginalName());
        $doc->setFilePath('uploads/documents/' . $filename);
        $doc->setFileType($file->getClientMimeType() ?? 'application/octet-stream');
        $doc->setFileSize($file->getSize());
        $doc->setTitle($request->request->get('title') ?: $file->getClientOriginalName());
        $doc->setTenantId($user->getTenantId());
        $this->em->persist($doc);
        $this->em->flush();

        return $this->redirectToRoute('leads.documents.index', ['lead_id' => $lead_id]);
    }

    #[Route('/{id}/edit', name: 'leads.documents.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $lead_id, int $id, InertiaInterface $inertia): Response
    {
        $doc = $this->findDocOr404($id, $lead_id);

        return $inertia->render('Leads/Documents/Edit', ['lead_id' => $lead_id, 'document' => $this->serialize($doc)]);
    }

    #[Route('/{id}', name: 'leads.documents.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $lead_id, int $id, Request $request): Response
    {
        $doc = $this->findDocOr404($id, $lead_id);
        if ($request->request->has('title')) {
            $doc->setTitle($request->request->get('title'));
        }
        $this->em->flush();

        return $this->redirectToRoute('leads.documents.index', ['lead_id' => $lead_id]);
    }

    #[Route('/{id}', name: 'leads.documents.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $lead_id, int $id): Response
    {
        $doc = $this->findDocOr404($id, $lead_id);
        $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $doc->getFilePath();
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
        $this->em->remove($doc);
        $this->em->flush();

        return $this->redirectToRoute('leads.documents.index', ['lead_id' => $lead_id]);
    }

    #[Route('/{id}/download', name: 'leads.documents.download', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function download(int $lead_id, int $id): Response
    {
        $doc      = $this->findDocOr404($id, $lead_id);
        $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $doc->getFilePath();

        if (!file_exists($fullPath)) {
            throw $this->createNotFoundException('File not found.');
        }

        return new BinaryFileResponse($fullPath);
    }

    #[Route('/{id}/preview', name: 'leads.documents.preview', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function preview(int $lead_id, int $id): Response
    {
        $doc      = $this->findDocOr404($id, $lead_id);
        $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $doc->getFilePath();

        if (!file_exists($fullPath)) {
            throw $this->createNotFoundException('File not found.');
        }

        return new BinaryFileResponse($fullPath, 200, [
            'Content-Disposition' => 'inline; filename="' . $doc->getFileName() . '"',
        ]);
    }

    private function findLeadOr404(int $leadId): void
    {
        /** @var User $user */
        $user = $this->getUser();
        $lead = $this->leadRepo->findOneBy(['id' => $leadId, 'tenantId' => $user->getTenantId()]);
        if (!$lead) throw $this->createNotFoundException('Lead not found.');
    }

    private function findDocOr404(int $id, int $leadId): Document
    {
        $doc = $this->docRepo->findOneBy(['id' => $id, 'leadId' => $leadId]);
        if (!$doc) throw $this->createNotFoundException('Document not found.');

        return $doc;
    }

    private function serialize(Document $d): array
    {
        return [
            'id'         => $d->getId(),
            'title'      => $d->getTitle(),
            'file_name'  => $d->getFileName(),
            'file_path'  => $d->getFilePath(),
            'file_type'  => $d->getFileType(),
            'file_size'  => $d->getFileSize(),
            'created_at' => $d->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
