<?php

namespace App\Controller\Users;

use App\Entity\Permission;
use App\Entity\Role;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/roles')]
class RoleController extends AbstractController
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
        private readonly PermissionRepository $permissionRepository,
        private readonly EntityManagerInterface $em,
        private readonly TenantContext $tenantContext,
        private readonly SluggerInterface $slugger,
    ) {}

    #[Route('', name: 'roles.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        $roles = $this->roleRepository->findAll();

        return $inertia->render('Roles/Index', [
            'roles' => array_map(fn(Role $r) => [
                'id' => $r->getId(),
                'name' => $r->getName(),
                'slug' => $r->getSlug(),
                'description' => $r->getDescription(),
                'is_active' => $r->isActive(),
                'permissions_count' => $r->getPermissions()->count(),
                'users_count' => $r->getUsers()->count(),
                'created_at' => $r->getCreatedAt()?->format('Y-m-d H:i:s'),
            ], $roles),
        ]);
    }

    #[Route('/create', name: 'roles.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Roles/Create', [
            'permissions' => $this->getGroupedPermissions(),
        ]);
    }

    #[Route('', name: 'roles.store', methods: ['POST'])]
    public function store(Request $request): RedirectResponse
    {
        $data = $request->request->all();

        $role = new Role();
        $role->setTenantId($this->tenantContext->getId());
        $role->setName($data['name']);
        $role->setSlug(strtolower((string) $this->slugger->slug($data['name'])));
        $role->setDescription($data['description'] ?? null);

        foreach (($data['permissions'] ?? []) as $permId) {
            $perm = $this->permissionRepository->find($permId);
            if ($perm) $role->getPermissions()->add($perm);
        }

        $this->em->persist($role);
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Role created successfully.');

        return $this->redirectToRoute('roles.index');
    }

    #[Route('/{id}/edit', name: 'roles.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        $role = $this->findOr404($id);

        return $inertia->render('Roles/Edit', [
            'role' => [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'slug' => $role->getSlug(),
                'description' => $role->getDescription(),
                'permissions' => $role->getPermissions()->map(fn(Permission $p) => $p->getId())->toArray(),
            ],
            'permissions' => $this->getGroupedPermissions(),
        ]);
    }

    #[Route('/{id}', name: 'roles.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): RedirectResponse
    {
        $role = $this->findOr404($id);
        $data = $request->request->all();

        $role->setName($data['name'] ?? $role->getName());
        $role->setDescription($data['description'] ?? $role->getDescription());

        // Sync permissions
        $role->getPermissions()->clear();
        foreach (($data['permissions'] ?? []) as $permId) {
            $perm = $this->permissionRepository->find($permId);
            if ($perm) $role->getPermissions()->add($perm);
        }

        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Role updated successfully.');

        return $this->redirectToRoute('roles.index');
    }

    #[Route('/{id}', name: 'roles.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id, Request $request): RedirectResponse
    {
        $this->em->remove($this->findOr404($id));
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Role deleted.');

        return $this->redirectToRoute('roles.index');
    }

    private function getGroupedPermissions(): array
    {
        $permissions = $this->permissionRepository->findAll();
        $grouped = [];
        foreach ($permissions as $p) {
            $grouped[$p->getGroup() ?? 'General'][] = [
                'id' => $p->getId(),
                'name' => $p->getName(),
                'slug' => $p->getSlug(),
            ];
        }
        return $grouped;
    }

    private function findOr404(int $id): Role
    {
        $role = $this->roleRepository->find($id);
        if (!$role) throw $this->createNotFoundException("Role #{$id} not found.");
        return $role;
    }
}
