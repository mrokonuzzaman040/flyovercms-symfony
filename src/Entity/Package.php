<?php

namespace App\Entity;

use App\Tenant\TenantScopedInterface;
use App\Repository\PackageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
#[ORM\Table(name: 'packages')]
class Package implements TenantScopedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $tenantId;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $totalPrice = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $currency = 'USD';

    #[ORM\Column(nullable: true)]
    private ?int $durationDays = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function setTenantId(int $tenantId): void { $this->tenantId = $tenantId; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function getTotalPrice(): ?string { return $this->totalPrice; }
    public function setTotalPrice(?string $totalPrice): void { $this->totalPrice = $totalPrice; }
    public function getCurrency(): ?string { return $this->currency; }
    public function setCurrency(?string $currency): void { $this->currency = $currency; }
    public function getDurationDays(): ?int { return $this->durationDays; }
    public function setDurationDays(?int $durationDays): void { $this->durationDays = $durationDays; }
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): void { $this->image = $image; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
