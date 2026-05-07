<?php

namespace App\Entity;

use App\Repository\TenantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TenantRepository::class)]
#[ORM\Table(name: 'tenants')]
class Tenant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $slug; // e.g. "flyoverbd" → flyoverbd.yourapp.com

    #[ORM\Column(length: 255)]
    private string $name; // e.g. "Flyover Bangladesh"

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customDomain = null; // e.g. "cms.flyoverbd.com"

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $timezone = 'UTC';

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $locale = 'en';

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getCustomDomain(): ?string { return $this->customDomain; }
    public function setCustomDomain(?string $customDomain): void { $this->customDomain = $customDomain; }

    public function getTimezone(): ?string { return $this->timezone; }
    public function setTimezone(?string $timezone): void { $this->timezone = $timezone; }

    public function getLocale(): ?string { return $this->locale; }
    public function setLocale(?string $locale): void { $this->locale = $locale; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
