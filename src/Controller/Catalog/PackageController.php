<?php

namespace App\Controller\Catalog;

use App\Entity\Package;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/packages')]
class PackageController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PackageRepository $repo,
    ) {}

    #[Route('', name: 'packages.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        $packages = $this->repo->findBy(['tenantId' => $this->getTenantId()], ['name' => 'ASC']);

        return $inertia->render('Packages/Index', [
            'packages' => array_map(fn(Package $p) => $this->serialize($p), $packages),
        ]);
    }

    #[Route('/create', name: 'packages.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Packages/Create');
    }

    #[Route('', name: 'packages.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $package = new Package();
        $package->setTenantId($this->getTenantId());
        $this->hydrate($package, $request->request->all());
        $this->em->persist($package);
        $this->em->flush();

        return $this->redirectToRoute('packages.index');
    }

    #[Route('/{id}', name: 'packages.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Packages/Show', ['package' => $this->serialize($this->findOr404($id))]);
    }

    #[Route('/{id}/edit', name: 'packages.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Packages/Edit', ['package' => $this->serialize($this->findOr404($id))]);
    }

    #[Route('/{id}', name: 'packages.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        $package = $this->findOr404($id);
        $this->hydrate($package, $request->request->all());
        $package->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->redirectToRoute('packages.show', ['id' => $id]);
    }

    #[Route('/{id}', name: 'packages.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): Response
    {
        $this->em->remove($this->findOr404($id));
        $this->em->flush();

        return $this->redirectToRoute('packages.index');
    }

    private function findOr404(int $id): Package
    {
        $p = $this->repo->findOneBy(['id' => $id, 'tenantId' => $this->getTenantId()]);
        if (!$p) throw $this->createNotFoundException();

        return $p;
    }

    private function hydrate(Package $p, array $data): void
    {
        if (isset($data['name'])) $p->setName($data['name']);
        if (isset($data['slug'])) $p->setSlug($data['slug']);
        if (array_key_exists('description', $data)) $p->setDescription($data['description'] ?: null);
        if (array_key_exists('total_price', $data)) $p->setTotalPrice($data['total_price'] ?: null);
        if (array_key_exists('currency', $data)) $p->setCurrency($data['currency'] ?: 'USD');
        if (array_key_exists('duration_days', $data)) $p->setDurationDays($data['duration_days'] ? (int) $data['duration_days'] : null);
        if (array_key_exists('image', $data)) $p->setImage($data['image'] ?: null);
        if (isset($data['is_active'])) $p->setIsActive((bool) $data['is_active']);
    }

    private function serialize(Package $p): array
    {
        return [
            'id'           => $p->getId(),
            'name'         => $p->getName(),
            'slug'         => $p->getSlug(),
            'description'  => $p->getDescription(),
            'total_price'  => $p->getTotalPrice(),
            'currency'     => $p->getCurrency(),
            'duration_days'=> $p->getDurationDays(),
            'image'        => $p->getImage(),
            'is_active'    => $p->isActive(),
            'created_at'   => $p->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    private function getTenantId(): int
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $user->getTenantId();
    }
}
