<?php

namespace App\Tenant;

/**
 * Marker interface. Any Doctrine entity implementing this
 * will be automatically filtered by tenant_id via TenantFilter.
 */
interface TenantScopedInterface
{
    public function getTenantId(): int;
    public function setTenantId(int $tenantId): void;
}
