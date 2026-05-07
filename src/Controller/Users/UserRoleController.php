<?php

namespace App\Controller\Users;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * User ↔ Role assignment management (bulk/overview).
 * Different from RoleController which manages role definitions.
 */
#[Route('/user-roles')]
class UserRoleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepo,
        private readonly RoleRepository $roleRepo,
    ) {}

    #[Route('', name: 'user-roles.index', methods: ['GET'])]
    public function index(Request $request, InertiaInterface $inertia): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $tenantId = $currentUser->getTenantId();

        $users = $this->userRepo->findBy(['tenantId' => $tenantId], ['name' => 'ASC']);
        $roles = $this->roleRepo->findBy([], ['name' => 'ASC']);

        $roleFilter = $request->query->get('role');
        $search     = $request->query->get('search');

        $filtered = array_filter($users, function (User $u) use ($roleFilter, $search) {
            if ($search && !str_contains(strtolower($u->getName()), strtolower($search))
                       && !str_contains(strtolower($u->getEmail()), strtolower($search))) {
                return false;
            }
            if ($roleFilter) {
                $roleSlugs = array_map(fn(Role $r) => $r->getName(), $u->getRoleEntities()->toArray());
                if (!in_array($roleFilter, $roleSlugs, true)) {
                    return false;
                }
            }
            return true;
        });

        $roleDistribution = [];
        foreach ($roles as $role) {
            $roleDistribution[] = [
                'name'  => $role->getName(),
                'count' => count(array_filter($users, fn(User $u) => $u->getRoleEntities()->contains($role))),
            ];
        }

        $serializeUser = function (User $u) {
            return [
                'id'     => $u->getId(),
                'name'   => $u->getName(),
                'email'  => $u->getEmail(),
                'avatar' => $u->getAvatar(),
                'roles'  => array_map(fn(Role $r) => ['id' => $r->getId(), 'name' => $r->getName()], $u->getRoleEntities()->toArray()),
            ];
        };

        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = 15;
        $items   = array_values($filtered);
        $total   = count($items);
        $slice   = array_slice($items, ($page - 1) * $perPage, $perPage);

        return $inertia->render('UserRoles/Index', [
            'users' => [
                'data'         => array_map($serializeUser, $slice),
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => (int) ceil($total / $perPage),
            ],
            'roles'            => array_map(fn(Role $r) => ['id' => $r->getId(), 'name' => $r->getName()], $roles),
            'statistics'       => [
                'total_users'            => count($users),
                'users_with_roles'       => count(array_filter($users, fn(User $u) => $u->getRoleEntities()->count() > 0)),
                'users_without_roles'    => count(array_filter($users, fn(User $u) => $u->getRoleEntities()->count() === 0)),
            ],
            'roleDistribution' => $roleDistribution,
            'filters'          => ['search' => $search, 'role' => $roleFilter],
        ]);
    }

    #[Route('/create', name: 'user-roles.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $users = $this->userRepo->findBy(['tenantId' => $currentUser->getTenantId()], ['name' => 'ASC']);
        $roles = $this->roleRepo->findBy([], ['name' => 'ASC']);

        return $inertia->render('UserRoles/Create', [
            'users' => array_map(fn(User $u) => ['id' => $u->getId(), 'name' => $u->getName(), 'email' => $u->getEmail()], $users),
            'roles' => array_map(fn(Role $r) => ['id' => $r->getId(), 'name' => $r->getName()], $roles),
        ]);
    }

    #[Route('', name: 'user-roles.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $data = $request->request->all();
        $user = $this->userRepo->find($data['user_id'] ?? 0) ?? throw $this->createNotFoundException('User not found');
        $roleIds = (array) ($data['role_ids'] ?? []);

        $user->getRoleEntities()->clear();
        foreach ($roleIds as $roleId) {
            $role = $this->roleRepo->find($roleId);
            if ($role) $user->addRole($role);
        }
        $this->em->flush();

        return $this->redirectToRoute('user-roles.show', ['id' => $user->getId()]);
    }

    #[Route('/{id}', name: 'user-roles.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        $user  = $this->userRepo->find($id) ?? throw $this->createNotFoundException();
        $roles = $this->roleRepo->findBy([], ['name' => 'ASC']);

        return $inertia->render('UserRoles/Show', [
            'user' => [
                'id'     => $user->getId(),
                'name'   => $user->getName(),
                'email'  => $user->getEmail(),
                'avatar' => $user->getAvatar(),
                'roles'  => array_map(fn(Role $r) => ['id' => $r->getId(), 'name' => $r->getName()], $user->getRoleEntities()->toArray()),
            ],
            'availableRoles' => array_map(fn(Role $r) => ['id' => $r->getId(), 'name' => $r->getName()], $roles),
        ]);
    }

    #[Route('/{id}/edit', name: 'user-roles.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        $user  = $this->userRepo->find($id) ?? throw $this->createNotFoundException();
        $roles = $this->roleRepo->findBy([], ['name' => 'ASC']);

        return $inertia->render('UserRoles/Edit', [
            'user' => [
                'id'       => $user->getId(),
                'name'     => $user->getName(),
                'email'    => $user->getEmail(),
                'role_ids' => $user->getRoleEntities()->map(fn(Role $r) => $r->getId())->toArray(),
            ],
            'roles' => array_map(fn(Role $r) => ['id' => $r->getId(), 'name' => $r->getName()], $roles),
        ]);
    }

    #[Route('/{id}', name: 'user-roles.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        $user    = $this->userRepo->find($id) ?? throw $this->createNotFoundException();
        $roleIds = (array) ($request->request->all()['role_ids'] ?? []);

        $user->getRoleEntities()->clear();
        foreach ($roleIds as $roleId) {
            $role = $this->roleRepo->find($roleId);
            if ($role) $user->addRole($role);
        }
        $this->em->flush();

        return $this->redirectToRoute('user-roles.show', ['id' => $id]);
    }
}
