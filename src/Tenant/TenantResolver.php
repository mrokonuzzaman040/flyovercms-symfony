<?php

namespace App\Tenant;

use App\Repository\TenantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves the current tenant from the request.
 * Strategy: subdomain (company1.yourapp.com) or X-Tenant-Slug header (for API).
 */
class TenantResolver
{
    public function __construct(
        private readonly TenantRepository $repository,
        private readonly TenantContext $context,
        private readonly string $rootDomain, // e.g. "flyovercms.com"
    ) {}

    public function resolve(Request $request): void
    {
        $slug = $this->extractSlug($request);

        if ($slug === null) {
            return; // Public routes (login, landing) don't need a tenant
        }

        $tenant = $this->repository->findOneBy(['slug' => $slug, 'isActive' => true]);

        if ($tenant === null) {
            throw new NotFoundHttpException(sprintf('Tenant "%s" not found or inactive.', $slug));
        }

        $this->context->set($tenant);
    }

    private function extractSlug(Request $request): ?string
    {
        // 1. Check X-Tenant-Slug header (API clients, testing)
        if ($request->headers->has('X-Tenant-Slug')) {
            return $request->headers->get('X-Tenant-Slug');
        }

        // 2. Subdomain resolution: company1.flyovercms.com → "company1"
        $host = $request->getHost();
        $rootDomain = ltrim($this->rootDomain, '.');

        if (str_ends_with($host, '.' . $rootDomain)) {
            $subdomain = substr($host, 0, -strlen('.' . $rootDomain));
            if ($subdomain && $subdomain !== 'www') {
                return $subdomain;
            }
        }

        return null;
    }
}
