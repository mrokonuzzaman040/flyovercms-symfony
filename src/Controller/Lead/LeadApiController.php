<?php

namespace App\Controller\Lead;

use App\Entity\Lead;
use App\Entity\LeadCallHistory;
use App\Entity\LeadFollowUp;
use App\Entity\LeadNote;
use App\Entity\User;
use App\Repository\BranchRepository;
use App\Repository\LeadCallHistoryRepository;
use App\Repository\LeadFollowUpRepository;
use App\Repository\LeadNoteRepository;
use App\Repository\LeadRepository;
use App\Repository\UserRepository;
use App\Service\Lead\LeadWriteService;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/leads')]
class LeadApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LeadRepository $leadRepo,
        private readonly LeadNoteRepository $noteRepo,
        private readonly LeadCallHistoryRepository $callRepo,
        private readonly LeadFollowUpRepository $followUpRepo,
        private readonly UserRepository $userRepo,
        private readonly BranchRepository $branchRepo,
        private readonly LeadWriteService $writeService,
    ) {}

    // ─── Quick Update (status, priority, assigned_to) ───────────────────────

    #[Route('/{id}/quick-update', name: 'leads.quick-update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function quickUpdate(int $id, Request $request): JsonResponse
    {
        $lead = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $allowed = array_intersect_key($data, array_flip(['status', 'priority', 'assigned_to']));

        $this->writeService->update($lead, $allowed, $user);

        return new JsonResponse(['success' => true, 'message' => 'Lead updated.']);
    }

    // ─── Quick Transfer ──────────────────────────────────────────────────────

    #[Route('/{id}/quick-transfer', name: 'leads.quick-transfer', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function quickTransfer(int $id, Request $request): JsonResponse
    {
        $lead = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $transferredTo = $data['transferred_to'] ?? null;

        if (!$transferredTo) {
            return new JsonResponse(['error' => 'transferred_to is required.'], 422);
        }

        $this->writeService->update($lead, ['assigned_to' => $transferredTo], $user);

        return new JsonResponse(['success' => true, 'message' => 'Lead transferred successfully.']);
    }

    // ─── Transfer to branch ──────────────────────────────────────────────────

    #[Route('/{id}/assign-branch', name: 'leads.assign-branch', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function assignBranch(int $id, Request $request): JsonResponse
    {
        $lead = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->writeService->update($lead, ['branch_id' => $data['branch_id'] ?? null], $user);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/bulk-assign-branch', name: 'leads.bulk-assign-branch', methods: ['POST'])]
    public function bulkAssignBranch(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $leadIds  = (array) ($data['lead_ids'] ?? []);
        $branchId = $data['branch_id'] ?? null;

        foreach ($leadIds as $leadId) {
            $lead = $this->leadRepo->findOneBy(['id' => $leadId, 'tenantId' => $user->getTenantId()]);
            if ($lead) {
                $this->writeService->update($lead, ['branch_id' => $branchId], $user);
            }
        }

        return new JsonResponse(['success' => true, 'count' => count($leadIds)]);
    }

    // ─── No-branch leads ────────────────────────────────────────────────────

    #[Route('/no-branch', name: 'leads.no-branch', methods: ['GET'])]
    public function noBranch(InertiaInterface $inertia): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $leads = $this->leadRepo->findBy(['tenantId' => $user->getTenantId(), 'branchId' => null], ['createdAt' => 'DESC']);

        return $inertia->render('Leads/NoBranch', [
            'leads' => array_map(fn(Lead $l) => $this->serializeLead($l), $leads),
        ]);
    }

    // ─── Overdue follow-ups ─────────────────────────────────────────────────

    #[Route('/overdue-follow-ups', name: 'leads.overdue-follow-ups', methods: ['GET'])]
    public function overdueFollowUps(InertiaInterface $inertia): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $followUps = $this->em->createQueryBuilder()
            ->select('f')
            ->from(LeadFollowUp::class, 'f')
            ->join(Lead::class, 'l', 'WITH', 'l.id = f.leadId')
            ->where('l.tenantId = :tid AND f.isCompleted = false AND f.followUpDate < :now')
            ->setParameters(['tid' => $user->getTenantId(), 'now' => new \DateTimeImmutable()])
            ->orderBy('f.followUpDate', 'ASC')
            ->getQuery()
            ->getResult();

        return $inertia->render('FollowUps/Overdue', [
            'followUps' => array_map(fn(LeadFollowUp $f) => $this->serializeFollowUp($f), $followUps),
        ]);
    }

    // ─── Notes ──────────────────────────────────────────────────────────────

    #[Route('/{id}/notes', name: 'leads.notes.store', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function storeNote(int $id, Request $request): JsonResponse
    {
        $lead = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $noteText = trim($data['note'] ?? '');

        if (!$noteText) {
            return new JsonResponse(['error' => 'Note cannot be empty.'], 422);
        }

        $note = new LeadNote();
        $note->setLeadId($lead->getId());
        $note->setCreatedBy($user->getId());
        $note->setNote($noteText);
        $this->em->persist($note);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'note'    => ['id' => $note->getId(), 'note' => $note->getNote(), 'created_at' => $note->getCreatedAt()->format('Y-m-d H:i:s')],
        ]);
    }

    // ─── Call History ────────────────────────────────────────────────────────

    #[Route('/{id}/call-history', name: 'leads.call-history', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function callHistory(int $id): JsonResponse
    {
        $lead    = $this->findOr404($id);
        $history = $this->callRepo->findBy(['leadId' => $lead->getId()], ['createdAt' => 'DESC']);

        return new JsonResponse([
            'call_history' => array_map(fn(LeadCallHistory $c) => [
                'id'           => $c->getId(),
                'phone_number' => $c->getPhoneNumber(),
                'call_type'    => $c->getCallType(),
                'status'       => $c->getStatus(),
                'duration'     => $c->getDuration(),
                'notes'        => $c->getNotes(),
                'call_date'    => $c->getCallDate()?->format('Y-m-d H:i:s'),
                'created_at'   => $c->getCreatedAt()->format('Y-m-d H:i:s'),
            ], $history),
        ]);
    }

    #[Route('/{id}/call-history', name: 'leads.call-history.store', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function storeCallHistory(int $id, Request $request): JsonResponse
    {
        $lead = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? $request->request->all();

        $call = new LeadCallHistory();
        $call->setLeadId($lead->getId());
        $call->setCalledBy($user->getId());
        $call->setPhoneNumber($data['phone_number'] ?? $lead->getPhone());
        $call->setCallType($data['call_type'] ?? 'outgoing');
        $call->setStatus($data['status'] ?? 'completed');
        $call->setDuration(isset($data['duration']) ? (int) $data['duration'] : null);
        $call->setNotes($data['notes'] ?? null);
        $this->em->persist($call);
        $this->em->flush();

        return new JsonResponse(['success' => true, 'id' => $call->getId()]);
    }

    // ─── Follow-up ───────────────────────────────────────────────────────────

    #[Route('/{id}/follow-up', name: 'leads.follow-up.store', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function storeFollowUp(int $id, Request $request): JsonResponse
    {
        $lead = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? $request->request->all();

        $followUp = new LeadFollowUp();
        $followUp->setLeadId($lead->getId());
        $followUp->setCreatedBy($user->getId());
        $followUp->setAssignedTo(isset($data['assigned_to']) ? (int) $data['assigned_to'] : $user->getId());
        $followUp->setFollowUpType($data['follow_up_type'] ?? 'call');
        $followUp->setCategory($data['category'] ?? 'general');
        $followUp->setPriority($data['priority'] ?? 'medium');
        $followUp->setNotes($data['notes'] ?? null);

        if (!empty($data['follow_up_date'])) {
            $dateStr = $data['follow_up_date'];
            if (!empty($data['follow_up_time'])) {
                $dateStr .= ' ' . $data['follow_up_time'];
            }
            try {
                $followUp->setFollowUpDate(new \DateTimeImmutable($dateStr));
            } catch (\Exception) {}
        }

        $this->em->persist($followUp);
        $this->em->flush();

        return new JsonResponse(['success' => true, 'id' => $followUp->getId()]);
    }

    // ─── Send communication ─────────────────────────────────────────────────

    #[Route('/{id}/send-communication', name: 'leads.send-communication', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function sendCommunication(int $id, Request $request): JsonResponse
    {
        $this->findOr404($id);
        // TODO: integrate with SMS/WhatsApp/Email providers
        return new JsonResponse(['success' => true, 'message' => 'Message queued for delivery.']);
    }

    // ─── Update services & packages ─────────────────────────────────────────

    #[Route('/{id}/update-services-packages', name: 'leads.update-services-packages', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function updateServicesPackages(int $id, Request $request): JsonResponse
    {
        $lead = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        // Store service_ids / package_ids as JSON on the lead (or dedicated pivot — scaffold for now)
        $this->writeService->update($lead, [
            'service_ids' => $data['selected_services'] ?? [],
            'package_ids' => $data['selected_packages'] ?? [],
        ], $user);

        return new JsonResponse(['success' => true]);
    }

    // ─── Order new service ───────────────────────────────────────────────────

    #[Route('/{id}/order-new-service', name: 'leads.order-new-service', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function orderNewService(int $id, Request $request): JsonResponse
    {
        $this->findOr404($id);
        // TODO: implement service order workflow
        return new JsonResponse(['success' => true, 'message' => 'Service order created.']);
    }

    // ─── Status history ─────────────────────────────────────────────────────

    #[Route('/{id}/status/history', name: 'leads.status.history', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function statusHistory(int $id): JsonResponse
    {
        $this->findOr404($id);
        // TODO: implement status change log (needs LeadStatusHistory entity)
        return new JsonResponse(['history' => []]);
    }

    // ─── Transfer (full flow) ────────────────────────────────────────────────

    #[Route('/{id}/transfer', name: 'leads.transfer', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function transferForm(int $id, InertiaInterface $inertia): Response
    {
        $lead = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();
        $users = $this->userRepo->findBy(['tenantId' => $user->getTenantId()], ['name' => 'ASC']);

        return $inertia->render('Leads/Transfer', [
            'lead'  => $this->serializeLead($lead),
            'users' => array_map(fn(User $u) => ['id' => $u->getId(), 'name' => $u->getName()], $users),
        ]);
    }

    #[Route('/{id}/transfer', name: 'leads.transfer.store', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function transfer(int $id, Request $request): JsonResponse
    {
        $lead = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->writeService->update($lead, ['assigned_to' => $data['transferred_to'] ?? null], $user);

        return new JsonResponse(['success' => true, 'message' => 'Lead transferred.']);
    }

    // ─── API: transfer users list ────────────────────────────────────────────

    #[Route('/{id}/transfer-users', name: 'api.leads.transfer-users', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function transferUsers(int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $users = $this->userRepo->findBy(['tenantId' => $currentUser->getTenantId()], ['name' => 'ASC']);

        return new JsonResponse([
            'users' => array_map(fn(User $u) => [
                'id'   => $u->getId(),
                'name' => $u->getName(),
            ], array_filter($users, fn(User $u) => $u->getId() !== $currentUser->getId())),
        ]);
    }

    // ─── Bulk upload ─────────────────────────────────────────────────────────

    #[Route('/bulk-upload', name: 'leads.bulk-upload', methods: ['GET'])]
    public function bulkUploadForm(InertiaInterface $inertia): Response
    {
        return $inertia->render('Leads/BulkUpload', []);
    }

    #[Route('/bulk-upload', name: 'leads.bulk-upload.store', methods: ['POST'])]
    public function bulkUploadStore(Request $request): JsonResponse
    {
        // TODO: parse CSV/Excel, create leads
        return new JsonResponse(['success' => true, 'message' => 'Bulk upload queued.']);
    }

    #[Route('/bulk-upload/{id}', name: 'leads.bulk-upload.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function bulkUploadUpdate(int $id, Request $request): JsonResponse
    {
        return new JsonResponse(['success' => true]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function findOr404(int $id): Lead
    {
        /** @var User $user */
        $user = $this->getUser();
        $lead = $this->leadRepo->findOneBy(['id' => $id, 'tenantId' => $user->getTenantId()]);
        if (!$lead) throw $this->createNotFoundException();

        return $lead;
    }

    private function serializeLead(Lead $l): array
    {
        return [
            'id'        => $l->getId(),
            'full_name' => $l->getFullName(),
            'phone'     => $l->getPhone(),
            'email'     => $l->getEmail(),
            'status'    => $l->getStatus(),
            'priority'  => $l->getPriority(),
            'branch_id' => $l->getBranchId(),
        ];
    }

    private function serializeFollowUp(LeadFollowUp $f): array
    {
        return [
            'id'             => $f->getId(),
            'lead_id'        => $f->getLeadId(),
            'follow_up_date' => $f->getFollowUpDate()?->format('Y-m-d H:i:s'),
            'follow_up_type' => $f->getFollowUpType(),
            'priority'       => $f->getPriority(),
            'status'         => $f->getStatus(),
            'notes'          => $f->getNotes(),
            'is_completed'   => $f->isCompleted(),
        ];
    }
}
