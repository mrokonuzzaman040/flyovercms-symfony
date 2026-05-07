<?php

namespace App\Service\Lead;

use App\Entity\Lead;
use App\Entity\User;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class LeadWriteService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TenantContext $tenantContext,
    ) {}

    public function create(array $data, User $creator): Lead
    {
        $lead = new Lead();
        $lead->setTenantId($this->tenantContext->getId());
        $lead->setCreatedBy($creator->getId());
        $lead->setUpdatedBy($creator->getId());

        $this->hydrate($lead, $data);

        $this->em->persist($lead);
        $this->em->flush();

        return $lead;
    }

    public function update(Lead $lead, array $data, User $updater): Lead
    {
        $lead->setUpdatedBy($updater->getId());
        $lead->setUpdatedAt(new \DateTimeImmutable());

        $this->hydrate($lead, $data);

        $this->em->flush();

        return $lead;
    }

    public function delete(Lead $lead): void
    {
        $this->em->remove($lead);
        $this->em->flush();
    }

    private function hydrate(Lead $lead, array $data): void
    {
        if (isset($data['full_name'])) $lead->setFullName($data['full_name']);
        if (isset($data['email'])) $lead->setEmail($data['email'] ?: null);
        if (isset($data['phone'])) $lead->setPhone($data['phone']);
        if (isset($data['alternate_phone'])) $lead->setAlternatePhone($data['alternate_phone']);
        if (isset($data['date_of_birth'])) $lead->setDateOfBirth($data['date_of_birth']);
        if (isset($data['gender'])) $lead->setGender($data['gender']);
        if (isset($data['nationality'])) $lead->setNationality($data['nationality']);
        if (isset($data['destination_country'])) $lead->setDestinationCountry($data['destination_country']);
        if (isset($data['status'])) $lead->setStatus($data['status']);
        if (isset($data['sub_status'])) $lead->setSubStatus($data['sub_status']);
        if (isset($data['priority'])) $lead->setPriority($data['priority']);
        if (isset($data['lead_source'])) $lead->setLeadSource($data['lead_source']);
        if (isset($data['notes'])) $lead->setNotes($data['notes']);
        if (isset($data['internal_notes'])) $lead->setInternalNotes($data['internal_notes']);
        if (isset($data['assigned_to'])) $lead->setAssignedTo($data['assigned_to'] ? (int)$data['assigned_to'] : null);
        if (isset($data['branch_id'])) $lead->setBranchId($data['branch_id'] ? (int)$data['branch_id'] : null);
        if (isset($data['vendor_id'])) $lead->setVendorId($data['vendor_id'] ? (int)$data['vendor_id'] : null);
        if (isset($data['service_selection_type'])) $lead->setServiceSelectionType($data['service_selection_type']);
        if (isset($data['selected_service_id'])) $lead->setSelectedServiceId($data['selected_service_id'] ? (int)$data['selected_service_id'] : null);
        if (isset($data['selected_package_id'])) $lead->setSelectedPackageId($data['selected_package_id'] ? (int)$data['selected_package_id'] : null);
        if (isset($data['transferred_to_department'])) $lead->setTransferredToDepartment($data['transferred_to_department']);
    }
}
