<?php

namespace App\Controller\Catalog;

use App\Entity\Vendor;
use App\Repository\VendorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vendors')]
class VendorController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VendorRepository $repo,
    ) {}

    #[Route('', name: 'vendors.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        $vendors = $this->repo->findBy(['tenantId' => $this->getTenantId()], ['name' => 'ASC']);

        return $inertia->render('Vendors/Index', [
            'vendors' => array_map(fn(Vendor $v) => $this->serialize($v), $vendors),
        ]);
    }

    #[Route('/create', name: 'vendors.create', methods: ['GET'])]
    public function create(InertiaInterface $inertia): Response
    {
        return $inertia->render('Vendors/Create');
    }

    #[Route('', name: 'vendors.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $vendor = new Vendor();
        $vendor->setTenantId($this->getTenantId());
        $this->hydrate($vendor, $request->request->all());
        $this->em->persist($vendor);
        $this->em->flush();

        return $this->redirectToRoute('vendors.index');
    }

    #[Route('/{id}', name: 'vendors.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        $vendor = $this->findOr404($id);

        return $inertia->render('Vendors/Show', ['vendor' => $this->serialize($vendor)]);
    }

    #[Route('/{id}/edit', name: 'vendors.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        $vendor = $this->findOr404($id);

        return $inertia->render('Vendors/Edit', ['vendor' => $this->serialize($vendor)]);
    }

    #[Route('/{id}', name: 'vendors.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): Response
    {
        $vendor = $this->findOr404($id);
        $this->hydrate($vendor, $request->request->all());
        $vendor->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->redirectToRoute('vendors.show', ['id' => $id]);
    }

    #[Route('/{id}', name: 'vendors.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): Response
    {
        $vendor = $this->findOr404($id);
        $this->em->remove($vendor);
        $this->em->flush();

        return $this->redirectToRoute('vendors.index');
    }

    private function findOr404(int $id): Vendor
    {
        $vendor = $this->repo->findOneBy(['id' => $id, 'tenantId' => $this->getTenantId()]);
        if (!$vendor) throw $this->createNotFoundException();

        return $vendor;
    }

    private function hydrate(Vendor $vendor, array $data): void
    {
        if (isset($data['name'])) $vendor->setName($data['name']);
        if (array_key_exists('email', $data)) $vendor->setEmail($data['email'] ?: null);
        if (array_key_exists('phone', $data)) $vendor->setPhone($data['phone'] ?: null);
        if (array_key_exists('alternate_phone', $data)) $vendor->setAlternatePhone($data['alternate_phone'] ?: null);
        if (array_key_exists('address', $data)) $vendor->setAddress($data['address'] ?: null);
        if (array_key_exists('city', $data)) $vendor->setCity($data['city'] ?: null);
        if (array_key_exists('state', $data)) $vendor->setState($data['state'] ?: null);
        if (array_key_exists('country', $data)) $vendor->setCountry($data['country'] ?: null);
        if (array_key_exists('postal_code', $data)) $vendor->setPostalCode($data['postal_code'] ?: null);
        if (array_key_exists('notes', $data)) $vendor->setNotes($data['notes'] ?: null);
        if (isset($data['is_active'])) $vendor->setIsActive((bool) $data['is_active']);
    }

    private function serialize(Vendor $v): array
    {
        return [
            'id'             => $v->getId(),
            'name'           => $v->getName(),
            'email'          => $v->getEmail(),
            'phone'          => $v->getPhone(),
            'alternate_phone'=> $v->getAlternatePhone(),
            'address'        => $v->getAddress(),
            'city'           => $v->getCity(),
            'state'          => $v->getState(),
            'country'        => $v->getCountry(),
            'postal_code'    => $v->getPostalCode(),
            'notes'          => $v->getNotes(),
            'is_active'      => $v->isActive(),
            'created_at'     => $v->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    private function getTenantId(): int
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $user->getTenantId();
    }
}
