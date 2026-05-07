<?php

namespace App\Controller\Users;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly EntityManagerInterface $em,
        private readonly TenantContext $tenantContext,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('', name: 'users.index', methods: ['GET'])]
    public function index(Request $request, InertiaInterface $inertia): Response
    {
        $users = $this->userRepository->findByTenant($this->tenantContext->getId());

        return $inertia->render('Users/Index', [
            'users' => array_map(fn(User $u) => $this->serialize($u), $users),
        ]);
    }

    #[Route('/create', name: 'users.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        $roles = $this->roleRepository->findAll();

        return $inertia->render('Users/Create', [
            'roles' => array_map(fn($r) => ['id' => $r->getId(), 'name' => $r->getName(), 'slug' => $r->getSlug()], $roles),
        ]);
    }

    #[Route('', name: 'users.store', methods: ['POST'])]
    public function store(Request $request): RedirectResponse
    {
        $data = $request->request->all();

        $user = new User();
        $user->setTenantId($this->tenantContext->getId());
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPhone($data['phone'] ?? null);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));

        if (!empty($data['role_id'])) {
            $role = $this->roleRepository->find($data['role_id']);
            if ($role) $user->addRole($role);
        }

        $this->em->persist($user);
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'User created successfully.');

        return $this->redirectToRoute('users.index');
    }

    #[Route('/{id}', name: 'users.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Users/Show', ['user' => $this->serialize($this->findOr404($id))]);
    }

    #[Route('/{id}/edit', name: 'users.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        $roles = $this->roleRepository->findAll();

        return $inertia->render('Users/Edit', [
            'user' => $this->serialize($this->findOr404($id)),
            'roles' => array_map(fn($r) => ['id' => $r->getId(), 'name' => $r->getName(), 'slug' => $r->getSlug()], $roles),
        ]);
    }

    #[Route('/{id}', name: 'users.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): RedirectResponse
    {
        $user = $this->findOr404($id);
        $data = $request->request->all();

        $user->setName($data['name'] ?? $user->getName());
        $user->setPhone($data['phone'] ?? $user->getPhone());
        $user->setTimezone($data['timezone'] ?? $user->getTimezone());

        if (!empty($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'User updated successfully.');

        return $this->redirectToRoute('users.index');
    }

    #[Route('/{id}', name: 'users.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id, Request $request): RedirectResponse
    {
        $this->em->remove($this->findOr404($id));
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('success', 'User deleted.');

        return $this->redirectToRoute('users.index');
    }

    private function findOr404(int $id): User
    {
        $user = $this->userRepository->find($id);
        if (!$user) throw $this->createNotFoundException("User #{$id} not found.");
        return $user;
    }

    private function serialize(User $u): array
    {
        return [
            'id' => $u->getId(),
            'name' => $u->getName(),
            'email' => $u->getEmail(),
            'avatar' => $u->getAvatar(),
            'phone' => $u->getPhone(),
            'timezone' => $u->getTimezone(),
            'language' => $u->getLanguage(),
            'branch_id' => $u->getBranchId(),
            'roles' => $u->getRoles(),
            'email_verified_at' => $u->getEmailVerifiedAt()?->format('Y-m-d H:i:s'),
            'created_at' => $u->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
