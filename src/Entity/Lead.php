<?php

namespace App\Entity;

use App\Repository\LeadRepository;
use App\Tenant\TenantScopedInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeadRepository::class)]
#[ORM\Table(name: 'leads')]
#[ORM\Index(columns: ['tenant_id', 'status', 'created_at'], name: 'idx_leads_tenant_status')]
#[ORM\Index(columns: ['tenant_id', 'assigned_to', 'status'], name: 'idx_leads_tenant_assigned')]
#[ORM\Index(columns: ['full_name'], name: 'idx_leads_full_name')]
#[ORM\Index(columns: ['branch_id', 'status'], name: 'idx_leads_branch_status')]
class Lead implements TenantScopedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $tenantId;

    #[ORM\Column(nullable: true)]
    private ?int $sourceLeadId = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $fullName = null;

    #[ORM\Column(length: 191, nullable: true, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 191, unique: true)]
    private string $phone;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $alternatePhone = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $dateOfBirth = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $placeOfBirth = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $nationality = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $currentCountry = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $maritalStatus = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $currentAddress = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $serviceType = null;

    #[ORM\Column(nullable: true)]
    private ?int $numberOfTourPersons = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $destinationCountry = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $secondaryDestinationCountry = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $budgetRange = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $fundingSource = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasBankStatement = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasDependents = false;

    #[ORM\Column(options: ['default' => 0])]
    private int $numberOfDependents = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $dependentsInformation = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasCv = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasOfferLetter = false;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $offerLetterPath = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $cvPath = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $internalNotes = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $specialRequirements = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $howDidYouFindUs = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $leadSource = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $leadSourceLink = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $referralSourceDetails = null;

    #[ORM\Column(length: 191, options: ['default' => 'new'])]
    private string $status = 'new';

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $subStatus = null;

    #[ORM\Column(length: 191, options: ['default' => 'normal'])]
    private string $priority = 'normal';

    #[ORM\Column(nullable: true)]
    private ?int $assignedTo = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $transferredToDepartment = null;

    #[ORM\Column(nullable: true)]
    private ?int $departmentManagerId = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isLockedForEditing = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $transferredToDepartmentAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $selectedServiceId = null;

    #[ORM\Column(nullable: true)]
    private ?int $selectedPackageId = null;

    #[ORM\Column(length: 50, nullable: true, enumType: null)]
    private ?string $serviceSelectionType = null;

    #[ORM\Column(nullable: true)]
    private ?int $branchId = null;

    #[ORM\Column(nullable: true)]
    private ?int $vendorId = null;

    #[ORM\Column(nullable: true)]
    private ?int $createdBy = null;

    #[ORM\Column(nullable: true)]
    private ?int $updatedBy = null;

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
    public function getFullName(): ?string { return $this->fullName; }
    public function setFullName(?string $fullName): void { $this->fullName = $fullName; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }
    public function getPhone(): string { return $this->phone; }
    public function setPhone(string $phone): void { $this->phone = $phone; }
    public function getAlternatePhone(): ?string { return $this->alternatePhone; }
    public function setAlternatePhone(?string $p): void { $this->alternatePhone = $p; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function getSubStatus(): ?string { return $this->subStatus; }
    public function setSubStatus(?string $s): void { $this->subStatus = $s; }
    public function getPriority(): string { return $this->priority; }
    public function setPriority(string $priority): void { $this->priority = $priority; }
    public function getAssignedTo(): ?int { return $this->assignedTo; }
    public function setAssignedTo(?int $userId): void { $this->assignedTo = $userId; }
    public function getBranchId(): ?int { return $this->branchId; }
    public function setBranchId(?int $branchId): void { $this->branchId = $branchId; }
    public function getVendorId(): ?int { return $this->vendorId; }
    public function setVendorId(?int $vendorId): void { $this->vendorId = $vendorId; }
    public function getCreatedBy(): ?int { return $this->createdBy; }
    public function setCreatedBy(?int $userId): void { $this->createdBy = $userId; }
    public function getUpdatedBy(): ?int { return $this->updatedBy; }
    public function setUpdatedBy(?int $userId): void { $this->updatedBy = $userId; }
    public function getSelectedServiceId(): ?int { return $this->selectedServiceId; }
    public function setSelectedServiceId(?int $id): void { $this->selectedServiceId = $id; }
    public function getSelectedPackageId(): ?int { return $this->selectedPackageId; }
    public function setSelectedPackageId(?int $id): void { $this->selectedPackageId = $id; }
    public function isLockedForEditing(): bool { return $this->isLockedForEditing; }
    public function setIsLockedForEditing(bool $locked): void { $this->isLockedForEditing = $locked; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): void { $this->notes = $notes; }
    public function getInternalNotes(): ?string { return $this->internalNotes; }
    public function setInternalNotes(?string $notes): void { $this->internalNotes = $notes; }
    public function getLeadSource(): ?string { return $this->leadSource; }
    public function setLeadSource(?string $source): void { $this->leadSource = $source; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $dt): void { $this->updatedAt = $dt; }

    // Remaining getters/setters follow same pattern — add as needed
    public function getDateOfBirth(): ?string { return $this->dateOfBirth; }
    public function setDateOfBirth(?string $dob): void { $this->dateOfBirth = $dob; }
    public function getGender(): ?string { return $this->gender; }
    public function setGender(?string $gender): void { $this->gender = $gender; }
    public function getNationality(): ?string { return $this->nationality; }
    public function setNationality(?string $nationality): void { $this->nationality = $nationality; }
    public function getDestinationCountry(): ?string { return $this->destinationCountry; }
    public function setDestinationCountry(?string $country): void { $this->destinationCountry = $country; }
    public function getTransferredToDepartment(): ?string { return $this->transferredToDepartment; }
    public function setTransferredToDepartment(?string $dept): void { $this->transferredToDepartment = $dept; }
    public function getDepartmentManagerId(): ?int { return $this->departmentManagerId; }
    public function setDepartmentManagerId(?int $id): void { $this->departmentManagerId = $id; }
    public function getServiceSelectionType(): ?string { return $this->serviceSelectionType; }
    public function setServiceSelectionType(?string $type): void { $this->serviceSelectionType = $type; }
    public function getSourceLeadId(): ?int { return $this->sourceLeadId; }
    public function setSourceLeadId(?int $id): void { $this->sourceLeadId = $id; }
}
