<?php

namespace App\Controller\Users;

use App\Entity\Permission;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/permissions')]
class PermissionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PermissionRepository $repo,
    ) {}

    #[Route('', name: 'permissions.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        $permissions = $this->repo->findBy([], ['group' => 'ASC', 'name' => 'ASC']);

        $grouped = [];
        foreach ($permissions as $p) {
            $grouped[$p->getGroup()][] = [
                'id'    => $p->getId(),
                'name'  => $p->getName(),
                'group' => $p->getGroup(),
            ];
        }

        return $inertia->render('Permissions/Index', [
            'permissions' => $permissions,
            'grouped'     => $grouped,
        ]);
    }

    #[Route('/create', name: 'permissions.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Permissions/Create');
    }

    #[Route('', name: 'permissions.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $data = $request->request->all();
        $permission = new Permission();
        $permission->setName($data['name']);
        $permission->setGroup($data['group'] ?? 'general');
        $this->em->persist($permission);
        $this->em->flush();

        return $this->redirectToRoute('permissions.index');
    }

    #[Route('/{id}', name: 'permissions.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        $permission = $this->repo->find($id) ?? throw $this->createNotFoundException();

        return $inertia->render('Permissions/Show', ['permission' => [
            'id' => $permission->getId(), 'name' => $permission->getName(), 'group' => $permission->getGroup(),
        ]]);
    }

    #[Route('/{id}/edit', name: 'permissions.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        $permission = $this->repo->find($id) ?? throw $this->createNotFoundException();

        return $inertia->render('Permissions/Edit', ['permission' => [
            'id' => $permission->getId(), 'name' => $permission->getName(), 'group' => $permission->getGroup(),
        ]]);
    }

    #[Route('/{id}', name: 'permissions.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        $permission = $this->repo->find($id) ?? throw $this->createNotFoundException();
        $data = $request->request->all();
        if (isset($data['name'])) $permission->setName($data['name']);
        if (isset($data['group'])) $permission->setGroup($data['group']);
        $this->em->flush();

        return $this->redirectToRoute('permissions.index');
    }

    #[Route('/{id}', name: 'permissions.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): Response
    {
        $permission = $this->repo->find($id) ?? throw $this->createNotFoundException();
        $this->em->remove($permission);
        $this->em->flush();

        return $this->redirectToRoute('permissions.index');
    }
}
