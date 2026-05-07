<?php

namespace App\Entity;

use App\Tenant\TenantScopedInterface;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notifications')]
class Notification implements TenantScopedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $tenantId;

    #[ORM\Column]
    private int $userId;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $priority = 'normal';

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $actionUrl = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $actionText = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $data = null;

    #[ORM\Column]
    private bool $isRead = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

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
    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): void { $this->userId = $userId; }
    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): void { $this->type = $type; }
    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $category): void { $this->category = $category; }
    public function getPriority(): ?string { return $this->priority; }
    public function setPriority(?string $priority): void { $this->priority = $priority; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): void { $this->message = $message; }
    public function getActionUrl(): ?string { return $this->actionUrl; }
    public function setActionUrl(?string $actionUrl): void { $this->actionUrl = $actionUrl; }
    public function getActionText(): ?string { return $this->actionText; }
    public function setActionText(?string $actionText): void { $this->actionText = $actionText; }
    public function getData(): ?array { return $this->data; }
    public function setData(?array $data): void { $this->data = $data; }
    public function isRead(): bool { return $this->isRead; }
    public function setIsRead(bool $isRead): void { $this->isRead = $isRead; }
    public function getReadAt(): ?\DateTimeImmutable { return $this->readAt; }
    public function setReadAt(?\DateTimeImmutable $readAt): void { $this->readAt = $readAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }

    public function markAsRead(): void
    {
        $this->isRead = true;
        $this->readAt = new \DateTimeImmutable();
    }

    public function markAsUnread(): void
    {
        $this->isRead = false;
        $this->readAt = null;
    }
}
