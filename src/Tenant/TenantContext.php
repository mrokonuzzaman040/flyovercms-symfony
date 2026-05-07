<?php

namespace App\Tenant;

use App\Entity\Tenant;
use RuntimeException;

/**
 * Holds the current tenant for the lifetime of a request.
 * Injected as a shared service — set once by TenantResolver, read everywhere.
 */
class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): Tenant
    {
        if ($this->tenant === null) {
            throw new RuntimeException('No tenant set in context. Is TenantResolver running?');
        }

        return $this->tenant;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    public function getId(): int
    {
        return $this->get()->getId();
    }
}
