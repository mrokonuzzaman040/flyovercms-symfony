<?php

namespace App\Entity;

use App\Tenant\TenantScopedInterface;
use App\Repository\ActivityLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityLogRepository::class)]
#[ORM\Table(name: 'activity_logs')]
class ActivityLog implements TenantScopedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $tenantId;

    #[ORM\Column(nullable: true)]
    private ?int $userId = null;

    #[ORM\Column(length: 100)]
    private string $action;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subjectType = null;

    #[ORM\Column(nullable: true)]
    private ?int $subjectId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $properties = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $userAgent = null;

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
    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): void { $this->userId = $userId; }
    public function getAction(): string { return $this->action; }
    public function setAction(string $action): void { $this->action = $action; }
    public function getSubjectType(): ?string { return $this->subjectType; }
    public function setSubjectType(?string $subjectType): void { $this->subjectType = $subjectType; }
    public function getSubjectId(): ?int { return $this->subjectId; }
    public function setSubjectId(?int $subjectId): void { $this->subjectId = $subjectId; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function getProperties(): ?array { return $this->properties; }
    public function setProperties(?array $properties): void { $this->properties = $properties; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): void { $this->ipAddress = $ipAddress; }
    public function getUserAgent(): ?string { return $this->userAgent; }
    public function setUserAgent(?string $userAgent): void { $this->userAgent = $userAgent; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
