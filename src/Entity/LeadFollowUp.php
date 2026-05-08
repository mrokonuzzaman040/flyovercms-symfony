<?php

namespace App\Entity;

use App\Repository\LeadFollowUpRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeadFollowUpRepository::class)]
#[ORM\Table(name: 'lead_follow_ups')]
class LeadFollowUp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $leadId;

    #[ORM\Column(nullable: true)]
    private ?int $createdBy = null;

    #[ORM\Column(nullable: true)]
    private ?int $assignedTo = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $followUpDate = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $followUpType = 'call';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $category = 'general';

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $priority = 'medium';

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $status = 'scheduled';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private bool $isCompleted = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $completedBy = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $outcome = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $outcomeNotes = null;

    #[ORM\Column(nullable: true)]
    private ?int $durationMinutes = null;

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
    public function getAssignedTo(): ?int { return $this->assignedTo; }
    public function setAssignedTo(?int $assignedTo): void { $this->assignedTo = $assignedTo; }
    public function getFollowUpDate(): ?\DateTimeImmutable { return $this->followUpDate; }
    public function setFollowUpDate(?\DateTimeImmutable $followUpDate): void { $this->followUpDate = $followUpDate; }
    public function getFollowUpType(): ?string { return $this->followUpType; }
    public function setFollowUpType(?string $followUpType): void { $this->followUpType = $followUpType; }
    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $category): void { $this->category = $category; }
    public function getPriority(): ?string { return $this->priority; }
    public function setPriority(?string $priority): void { $this->priority = $priority; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): void { $this->status = $status; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function isCompleted(): bool { return $this->isCompleted; }
    public function setIsCompleted(bool $isCompleted): void { $this->isCompleted = $isCompleted; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeImmutable $completedAt): void { $this->completedAt = $completedAt; }
    public function getCompletedBy(): ?int { return $this->completedBy; }
    public function setCompletedBy(?int $completedBy): void { $this->completedBy = $completedBy; }
    public function getOutcome(): ?string { return $this->outcome; }
    public function setOutcome(?string $outcome): void { $this->outcome = $outcome; }
    public function getOutcomeNotes(): ?string { return $this->outcomeNotes; }
    public function setOutcomeNotes(?string $outcomeNotes): void { $this->outcomeNotes = $outcomeNotes; }
    public function getDurationMinutes(): ?int { return $this->durationMinutes; }
    public function setDurationMinutes(?int $durationMinutes): void { $this->durationMinutes = $durationMinutes; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
