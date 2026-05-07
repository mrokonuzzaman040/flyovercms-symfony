<?php

namespace App\Controller\Catalog;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/services')]
class ServiceController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ServiceRepository $repo,
    ) {}

    #[Route('', name: 'services.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        $services = $this->repo->findBy(['tenantId' => $this->getTenantId()], ['name' => 'ASC']);

        return $inertia->render('Services/Index', [
            'services' => array_map(fn(Service $s) => $this->serialize($s), $services),
            'types'    => Service::TYPES,
        ]);
    }

    #[Route('/create', name: 'services.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Services/Create', ['types' => Service::TYPES]);
    }

    #[Route('', name: 'services.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $service = new Service();
        $service->setTenantId($this->getTenantId());
        $this->hydrate($service, $request->request->all());
        $this->em->persist($service);
        $this->em->flush();

        return $this->redirectToRoute('services.index');
    }

    #[Route('/{id}', name: 'services.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Services/Show', ['service' => $this->serialize($this->findOr404($id))]);
    }

    #[Route('/{id}/edit', name: 'services.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Services/Edit', [
            'service' => $this->serialize($this->findOr404($id)),
            'types'   => Service::TYPES,
        ]);
    }

    #[Route('/{id}', name: 'services.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        $service = $this->findOr404($id);
        $this->hydrate($service, $request->request->all());
        $service->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->redirectToRoute('services.show', ['id' => $id]);
    }

    #[Route('/{id}', name: 'services.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): Response
    {
        $this->em->remove($this->findOr404($id));
        $this->em->flush();

        return $this->redirectToRoute('services.index');
    }

    private function findOr404(int $id): Service
    {
        $s = $this->repo->findOneBy(['id' => $id, 'tenantId' => $this->getTenantId()]);
        if (!$s) throw $this->createNotFoundException();

        return $s;
    }

    private function hydrate(Service $s, array $data): void
    {
        if (isset($data['name'])) $s->setName($data['name']);
        if (isset($data['slug'])) $s->setSlug($data['slug']);
        if (array_key_exists('description', $data)) $s->setDescription($data['description'] ?: null);
        if (array_key_exists('type', $data)) $s->setType($data['type'] ?: null);
        if (array_key_exists('price', $data)) $s->setPrice($data['price'] ?: null);
        if (array_key_exists('currency', $data)) $s->setCurrency($data['currency'] ?: 'USD');
        if (array_key_exists('duration_days', $data)) $s->setDurationDays($data['duration_days'] ? (int) $data['duration_days'] : null);
        if (array_key_exists('image', $data)) $s->setImage($data['image'] ?: null);
        if (isset($data['is_active'])) $s->setIsActive((bool) $data['is_active']);
    }

    private function serialize(Service $s): array
    {
        return [
            'id'           => $s->getId(),
            'name'         => $s->getName(),
            'slug'         => $s->getSlug(),
            'description'  => $s->getDescription(),
            'type'         => $s->getType(),
            'price'        => $s->getPrice(),
            'currency'     => $s->getCurrency(),
            'duration_days'=> $s->getDurationDays(),
            'image'        => $s->getImage(),
            'is_active'    => $s->isActive(),
            'created_at'   => $s->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    private function getTenantId(): int
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $user->getTenantId();
    }
}
