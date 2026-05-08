<?php

namespace App\Controller\Organization;

use App\Entity\Branch;
use App\Entity\User;
use App\Repository\BranchRepository;
use App\Repository\LeadRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/branches/{id}', requirements: ['id' => '\d+'])]
class BranchDashboardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly BranchRepository $branchRepo,
        private readonly UserRepository $userRepo,
        private readonly LeadRepository $leadRepo,
    ) {}

    #[Route('/dashboard', name: 'branch.dashboard', methods: ['GET'])]
    public function dashboard(int $id, InertiaInterface $inertia): Response
    {
        $branch = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $leads = $this->leadRepo->findBy(['branchId' => $id, 'tenantId' => $user->getTenantId()], ['createdAt' => 'DESC'], 10);
        $users = $this->userRepo->findBy(['branchId' => $id, 'tenantId' => $user->getTenantId()]);

        return $inertia->render('Branches/Dashboard', [
            'branch' => $this->serializeBranch($branch),
            'stats'  => [
                'total_leads' => $this->leadRepo->count(['branchId' => $id]),
                'total_users' => count($users),
            ],
            'recentLeads' => array_map(fn($l) => ['id' => $l->getId(), 'full_name' => $l->getFullName(), 'status' => $l->getStatus()], $leads),
        ]);
    }

    #[Route('/leads', name: 'branch.leads', methods: ['GET'])]
    public function leads(int $id, InertiaInterface $inertia): Response
    {
        $branch = $this->findOr404($id);
        /** @var User $user */
        $user = $this->getUser();

        $leads = $this->leadRepo->findBy(['branchId' => $id, 'tenantId' => $user->getTenantId()], ['createdAt' => 'DESC']);

        return $inertia->render('Branches/Leads', [
            'branch' => $this->serializeBranch($branch),
            'leads'  => array_map(fn($l) => ['id' => $l->getId(), 'full_name' => $l->getFullName(), 'status' => $l->getStatus(), 'phone' => $l->getPhone()], $leads),
        ]);
    }

    #[Route('/users', name: 'branch.users', methods: ['GET'])]
    public function users(int $id, InertiaInterface $inertia): Response
    {
        $branch = $this->findOr404($id);
        /** @var User $user */
        $currentUser = $this->getUser();

        $users = $this->userRepo->findBy(['branchId' => $id, 'tenantId' => $currentUser->getTenantId()]);

        return $inertia->render('Branches/Users', [
            'branch' => $this->serializeBranch($branch),
            'users'  => array_map(fn(User $u) => ['id' => $u->getId(), 'name' => $u->getName(), 'email' => $u->getEmail()], $users),
        ]);
    }

    #[Route('/users', name: 'branch.assign-user', methods: ['POST'])]
    public function assignUser(int $id, Request $request): JsonResponse
    {
        $branch = $this->findOr404($id);
        $data   = json_decode($request->getContent(), true) ?? $request->request->all();
        $userId = $data['user_id'] ?? null;

        if (!$userId) {
            return new JsonResponse(['error' => 'user_id required'], 422);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $targetUser = $this->userRepo->findOneBy(['id' => $userId, 'tenantId' => $currentUser->getTenantId()]);
        if (!$targetUser) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $targetUser->setBranchId($branch->getId());
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/users/{userId}', name: 'branch.remove-user', methods: ['DELETE'], requirements: ['userId' => '\d+'])]
    public function removeUser(int $id, int $userId): JsonResponse
    {
        $this->findOr404($id);
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $targetUser = $this->userRepo->findOneBy(['id' => $userId, 'tenantId' => $currentUser->getTenantId()]);
        if (!$targetUser) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $targetUser->setBranchId(null);
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }

    private function findOr404(int $id): Branch
    {
        $b = $this->branchRepo->find($id);
        if (!$b) throw $this->createNotFoundException();

        return $b;
    }

    private function serializeBranch(Branch $b): array
    {
        return ['id' => $b->getId(), 'name' => $b->getName(), 'code' => $b->getCode()];
    }
}
