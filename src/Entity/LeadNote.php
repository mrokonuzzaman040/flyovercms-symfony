<?php

namespace App\Entity;

use App\Repository\LeadNoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeadNoteRepository::class)]
#[ORM\Table(name: 'lead_notes')]
class LeadNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $leadId;

    #[ORM\Column(nullable: true)]
    private ?int $createdBy = null;

    #[ORM\Column(type: 'text')]
    private string $note;

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
    public function getLeadId(): int { return $this->leadId; }
    public function setLeadId(int $leadId): void { $this->leadId = $leadId; }
    public function getCreatedBy(): ?int { return $this->createdBy; }
    public function setCreatedBy(?int $createdBy): void { $this->createdBy = $createdBy; }
    public function getNote(): string { return $this->note; }
    public function setNote(string $note): void { $this->note = $note; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
