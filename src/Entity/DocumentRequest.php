<?php

namespace App\Entity;

use App\Tenant\TenantScopedInterface;
use App\Repository\DocumentRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRequestRepository::class)]
#[ORM\Table(name: 'document_requests')]
class DocumentRequest implements TenantScopedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $tenantId;

    #[ORM\Column]
    private int $leadId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(length: 50)]
    private string $status = 'pending';

    #[ORM\Column(nullable: true)]
    private ?int $requestedBy = null;

    #[ORM\Column(nullable: true)]
    private ?int $fulfilledBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $fulfilledAt = null;

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
    public function getLeadId(): int { return $this->leadId; }
    public function setLeadId(int $leadId): void { $this->leadId = $leadId; }
    public function getDocumentType(): ?string { return $this->documentType; }
    public function setDocumentType(?string $documentType): void { $this->documentType = $documentType; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): void { $this->message = $message; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function getRequestedBy(): ?int { return $this->requestedBy; }
    public function setRequestedBy(?int $requestedBy): void { $this->requestedBy = $requestedBy; }
    public function getFulfilledBy(): ?int { return $this->fulfilledBy; }
    public function setFulfilledBy(?int $fulfilledBy): void { $this->fulfilledBy = $fulfilledBy; }
    public function getFulfilledAt(): ?\DateTimeImmutable { return $this->fulfilledAt; }
    public function setFulfilledAt(?\DateTimeImmutable $fulfilledAt): void { $this->fulfilledAt = $fulfilledAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
