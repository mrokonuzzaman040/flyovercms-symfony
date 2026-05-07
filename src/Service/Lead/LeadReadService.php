<?php

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Entity\User;
use App\Repository\BranchRepository;
use App\Repository\DepartmentRepository;
use App\Repository\LeadRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class LeadReadService
{
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly UserRepository $userRepository,
        private readonly BranchRepository $branchRepository,
        private readonly DepartmentRepository $departmentRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    public function getIndexPageData(Request $request): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('l')
            ->from(Lead::class, 'l')
            ->orderBy('l.createdAt', 'DESC');

        if ($search = $request->query->get('search')) {
            $qb->andWhere('l.fullName LIKE :s OR l.phone LIKE :s OR l.email LIKE :s')
               ->setParameter('s', "%{$search}%");
        }

        if ($status = $request->query->get('status')) {
            $qb->andWhere('l.status = :status')->setParameter('status', $status);
        }

        if ($priority = $request->query->get('priority')) {
            $qb->andWhere('l.priority = :priority')->setParameter('priority', $priority);
        }

        if ($branchId = $request->query->get('branch_id')) {
            $qb->andWhere('l.branchId = :branchId')->setParameter('branchId', $branchId);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = (int) $request->query->get('per_page', 15);

        $total = (clone $qb)->select('COUNT(l.id)')->getQuery()->getSingleScalarResult();

        $leads = $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return [
            'leads' => [
                'data' => array_map(fn(Lead $l) => $this->serializeLead($l), $leads),
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => (int) $total,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ],
            'filters' => [
                'search' => $request->query->get('search'),
                'status' => $request->query->get('status'),
                'priority' => $request->query->get('priority'),
                'branch_id' => $request->query->get('branch_id'),
            ],
            'branches' => array_map(
                fn($b) => ['id' => $b->getId(), 'name' => $b->getName()],
                $this->branchRepository->findBy(['isActive' => true], ['name' => 'ASC'])
            ),
            'statuses' => self::statuses(),
            'priorities' => self::priorities(),
        ];
    }

    public function getCreatePageData(User $user): array
    {
        return [
            'branches' => array_map(
                fn($b) => ['id' => $b->getId(), 'name' => $b->getName()],
                $this->branchRepository->findBy(['isActive' => true], ['name' => 'ASC'])
            ),
            'users' => array_map(
                fn($u) => ['id' => $u->getId(), 'name' => $u->getName()],
                $this->userRepository->findByTenant($user->getTenantId())
            ),
            'departments' => array_map(
                fn($d) => ['id' => $d->getId(), 'name' => $d->getName(), 'code' => $d->getCode()],
                $this->departmentRepository->findBy(['isActive' => true], ['sortOrder' => 'ASC'])
            ),
            'statuses' => self::statuses(),
            'priorities' => self::priorities(),
        ];
    }

    public function getShowPageData(Lead $lead): array
    {
        return [
            'lead' => $this->serializeLead($lead, detail: true),
        ];
    }

    public function getEditPageData(User $user, Lead $lead): array
    {
        return array_merge(
            $this->getCreatePageData($user),
            ['lead' => $this->serializeLead($lead, detail: true)]
        );
    }

    private function serializeLead(Lead $lead, bool $detail = false): array
    {
        $data = [
            'id' => $lead->getId(),
            'full_name' => $lead->getFullName(),
            'email' => $lead->getEmail(),
            'phone' => $lead->getPhone(),
            'alternate_phone' => $lead->getAlternatePhone(),
            'status' => $lead->getStatus(),
            'sub_status' => $lead->getSubStatus(),
            'priority' => $lead->getPriority(),
            'assigned_to' => $lead->getAssignedTo(),
            'branch_id' => $lead->getBranchId(),
            'lead_source' => $lead->getLeadSource(),
            'destination_country' => $lead->getDestinationCountry(),
            'created_at' => $lead->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updated_at' => $lead->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];

        if ($detail) {
            $data = array_merge($data, [
                'gender' => $lead->getGender(),
                'nationality' => $lead->getNationality(),
                'date_of_birth' => $lead->getDateOfBirth(),
                'notes' => $lead->getNotes(),
                'internal_notes' => $lead->getInternalNotes(),
                'is_locked_for_editing' => $lead->isLockedForEditing(),
                'transferred_to_department' => $lead->getTransferredToDepartment(),
                'service_selection_type' => $lead->getServiceSelectionType(),
                'selected_service_id' => $lead->getSelectedServiceId(),
                'selected_package_id' => $lead->getSelectedPackageId(),
            ]);
        }

        return $data;
    }

    public static function statuses(): array
    {
        return ['new', 'contacted', 'qualified', 'follow_up', 'converted', 'closed', 'cancelled'];
    }

    public static function priorities(): array
    {
        return ['low', 'normal', 'high', 'urgent'];
    }
}
