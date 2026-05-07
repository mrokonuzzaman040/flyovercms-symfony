<?php

namespace App\Controller\Organization;

use App\Entity\Branch;
use App\Entity\User;
use App\Repository\BranchRepository;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\UnicodeString;

#[Route('/branches')]
class BranchController extends AbstractController
{
    public function __construct(
        private readonly BranchRepository $branchRepository,
        private readonly EntityManagerInterface $em,
        private readonly TenantContext $tenantContext,
    ) {}

    #[Route('', name: 'branches.index', methods: ['GET'])]
    public function index(Request $request, InertiaInterface $inertia): Response
    {
        $qb = $this->em->createQueryBuilder()
            ->select('b')->from(Branch::class, 'b')
            ->orderBy('b.createdAt', 'DESC');

        if ($search = $request->query->get('search')) {
            $qb->andWhere('b.name LIKE :s OR b.code LIKE :s')->setParameter('s', "%{$search}%");
        }

        $branches = $qb->getQuery()->getResult();

        return $inertia->render('Branches/Index', [
            'branches' => array_map(fn(Branch $b) => [
                'id' => $b->getId(),
                'name' => $b->getName(),
                'code' => $b->getCode(),
                'address' => $b->getAddress(),
                'phone' => $b->getPhone(),
                'email' => $b->getEmail(),
                'is_active' => $b->isActive(),
                'created_at' => $b->getCreatedAt()?->format('Y-m-d H:i:s'),
            ], $branches),
            'filters' => ['search' => $request->query->get('search')],
        ]);
    }

    #[Route('/create', name: 'branches.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Branches/Create');
    }

    #[Route('', name: 'branches.store', methods: ['POST'])]
    public function store(Request $request): RedirectResponse
    {
        $data = $request->request->all();
        $branch = new Branch();
        $branch->setTenantId($this->tenantContext->getId());
        $branch->setName($data['name']);
        $branch->setCode($this->generateCode($data['name']));
        $branch->setAddress($data['address'] ?? null);
        $branch->setPhone($data['phone'] ?? null);
        $branch->setEmail($data['email'] ?? null);
        $branch->setIsActive(true);

        $this->em->persist($branch);
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Branch created successfully.');

        return $this->redirectToRoute('branches.index');
    }

    #[Route('/{id}', name: 'branches.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        $branch = $this->findOr404($id);

        return $inertia->render('Branches/Show', ['branch' => $this->serialize($branch)]);
    }

    #[Route('/{id}/edit', name: 'branches.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Branches/Edit', ['branch' => $this->serialize($this->findOr404($id))]);
    }

    #[Route('/{id}', name: 'branches.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): RedirectResponse
    {
        $branch = $this->findOr404($id);
        $data = $request->request->all();

        $branch->setName($data['name'] ?? $branch->getName());
        $branch->setAddress($data['address'] ?? $branch->getAddress());
        $branch->setPhone($data['phone'] ?? $branch->getPhone());
        $branch->setEmail($data['email'] ?? $branch->getEmail());
        $branch->setIsActive((bool) ($data['is_active'] ?? $branch->isActive()));

        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Branch updated successfully.');

        return $this->redirectToRoute('branches.index');
    }

    #[Route('/{id}', name: 'branches.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id, Request $request): RedirectResponse
    {
        $this->em->remove($this->findOr404($id));
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Branch deleted successfully.');

        return $this->redirectToRoute('branches.index');
    }

    private function generateCode(string $name): string
    {
        $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        $base = substr($base ?: 'BR', 0, 8);
        $code = $base;
        $i = 1;
        while ($this->branchRepository->findOneBy(['code' => $code])) {
            $code = substr($base, 0, 7) . $i++;
        }
        return $code;
    }

    private function findOr404(int $id): Branch
    {
        $branch = $this->branchRepository->find($id);
        if (!$branch) throw $this->createNotFoundException("Branch #{$id} not found.");
        return $branch;
    }

    private function serialize(Branch $b): array
    {
        return [
            'id' => $b->getId(),
            'name' => $b->getName(),
            'code' => $b->getCode(),
            'description' => $b->getDescription(),
            'address' => $b->getAddress(),
            'phone' => $b->getPhone(),
            'email' => $b->getEmail(),
            'is_active' => $b->isActive(),
            'created_at' => $b->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
