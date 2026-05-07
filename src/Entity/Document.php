<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use App\Tenant\TenantScopedInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: 'documents')]
#[ORM\Index(columns: ['lead_id'], name: 'idx_documents_lead_id')]
#[ORM\Index(columns: ['document_type'], name: 'idx_documents_type')]
class Document implements TenantScopedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $tenantId;

    #[ORM\Column]
    private int $leadId;

    #[ORM\Column(length: 191)]
    private string $name;

    #[ORM\Column(length: 191)]
    private string $originalName;

    #[ORM\Column(length: 191)]
    private string $filePath;

    #[ORM\Column(length: 191)]
    private string $fileType;

    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    private int $fileSize;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $documentType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $uploadedBy = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function setTenantId(int $tenantId): void { $this->tenantId = $tenantId; }
    public function getLeadId(): int { return $this->leadId; }
    public function setLeadId(int $leadId): void { $this->leadId = $leadId; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function getOriginalName(): string { return $this->originalName; }
    public function setOriginalName(string $name): void { $this->originalName = $name; }
    public function getFilePath(): string { return $this->filePath; }
    public function setFilePath(string $path): void { $this->filePath = $path; }
    public function getFileType(): string { return $this->fileType; }
    public function setFileType(string $type): void { $this->fileType = $type; }
    public function getFileSize(): int { return $this->fileSize; }
    public function setFileSize(int $size): void { $this->fileSize = $size; }
    public function getDocumentType(): ?string { return $this->documentType; }
    public function setDocumentType(?string $type): void { $this->documentType = $type; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): void { $this->description = $d; }
    public function getUploadedBy(): ?int { return $this->uploadedBy; }
    public function setUploadedBy(?int $userId): void { $this->uploadedBy = $userId; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}
