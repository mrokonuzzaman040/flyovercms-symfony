<?php

namespace App\Entity;

use App\Repository\LeadCallHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeadCallHistoryRepository::class)]
#[ORM\Table(name: 'lead_call_histories')]
class LeadCallHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $leadId;

    #[ORM\Column(nullable: true)]
    private ?int $calledBy = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $callType = 'outgoing';

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $status = 'completed';

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $callDate = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->callDate  = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getLeadId(): int { return $this->leadId; }
    public function setLeadId(int $leadId): void { $this->leadId = $leadId; }
    public function getCalledBy(): ?int { return $this->calledBy; }
    public function setCalledBy(?int $calledBy): void { $this->calledBy = $calledBy; }
    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function setPhoneNumber(?string $phoneNumber): void { $this->phoneNumber = $phoneNumber; }
    public function getCallType(): ?string { return $this->callType; }
    public function setCallType(?string $callType): void { $this->callType = $callType; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): void { $this->status = $status; }
    public function getDuration(): ?int { return $this->duration; }
    public function setDuration(?int $duration): void { $this->duration = $duration; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function getCallDate(): ?\DateTimeImmutable { return $this->callDate; }
    public function setCallDate(?\DateTimeImmutable $callDate): void { $this->callDate = $callDate; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
