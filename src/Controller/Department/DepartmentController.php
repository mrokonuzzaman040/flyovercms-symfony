<?php

namespace App\Controller\Department;

use App\Entity\Department;
use App\Repository\DepartmentRepository;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/departments')]
class DepartmentController extends AbstractController
{
    public function __construct(
        private readonly DepartmentRepository $departmentRepository,
        private readonly EntityManagerInterface $em,
        private readonly TenantContext $tenantContext,
    ) {}

    #[Route('', name: 'departments.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        $departments = $this->departmentRepository->findBy([], ['sortOrder' => 'ASC']);

        return $inertia->render('Departments/Index', [
            'departments' => array_map(fn(Department $d) => $this->serialize($d), $departments),
        ]);
    }

    #[Route('/accounts', name: 'departments.accounts', methods: ['GET'])]
    public function accounts(InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Accounts');
    }

    #[Route('/visa', name: 'departments.visa', methods: ['GET'])]
    public function visa(InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Visa');
    }

    #[Route('/create', name: 'departments.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Create');
    }

    #[Route('', name: 'departments.store', methods: ['POST'])]
    public function store(Request $request): RedirectResponse
    {
        $data = $request->request->all();
        $dept = new Department();
        $dept->setTenantId($this->tenantContext->getId());
        $dept->setName($data['name']);
        $dept->setCode(strtoupper($data['code'] ?? substr(preg_replace('/[^A-Za-z0-9]/', '', $data['name']), 0, 8)));
        $dept->setDescription($data['description'] ?? null);
        $dept->setIsActive(true);
        $dept->setSortOrder((int) ($data['sort_order'] ?? 0));

        $this->em->persist($dept);
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Department created successfully.');

        return $this->redirectToRoute('departments.index');
    }

    #[Route('/{id}/edit', name: 'departments.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Departments/Edit', ['department' => $this->serialize($this->findOr404($id))]);
    }

    #[Route('/{id}', name: 'departments.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): RedirectResponse
    {
        $dept = $this->findOr404($id);
        $data = $request->request->all();

        $dept->setName($data['name'] ?? $dept->getName());
        $dept->setDescription($data['description'] ?? $dept->getDescription());
        $dept->setIsActive((bool)($data['is_active'] ?? $dept->isActive()));
        $dept->setSortOrder((int)($data['sort_order'] ?? $dept->getSortOrder()));

        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Department updated successfully.');

        return $this->redirectToRoute('departments.index');
    }

    #[Route('/{id}', name: 'departments.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id, Request $request): RedirectResponse
    {
        $this->em->remove($this->findOr404($id));
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Department deleted.');

        return $this->redirectToRoute('departments.index');
    }

    private function findOr404(int $id): Department
    {
        $dept = $this->departmentRepository->find($id);
        if (!$dept) throw $this->createNotFoundException("Department #{$id} not found.");
        return $dept;
    }

    private function serialize(Department $d): array
    {
        return [
            'id' => $d->getId(),
            'code' => $d->getCode(),
            'name' => $d->getName(),
            'description' => $d->getDescription(),
            'is_active' => $d->isActive(),
            'sort_order' => $d->getSortOrder(),
            'created_at' => $d->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
