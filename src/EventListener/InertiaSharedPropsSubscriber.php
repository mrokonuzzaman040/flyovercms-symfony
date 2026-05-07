<?php

namespace App\EventListener;

use App\Entity\User;
use App\Tenant\TenantContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;

/**
 * Shares auth user, tenant, and flash messages with every Inertia response.
 * Equivalent to HandleInertiaRequests middleware in Laravel.
 */
class InertiaSharedPropsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly InertiaInterface $inertia,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RequestStack $requestStack,
        private readonly TenantContext $tenantContext,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 5], // After TenantSubscriber (priority 10)
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->inertia->share([
            'auth' => fn () => $this->resolveAuth(),
            'tenant' => fn () => $this->resolveTenant(),
            'flash' => fn () => $this->resolveFlash(),
        ]);
    }

    private function resolveAuth(): array
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user instanceof User) {
            return ['user' => null];
        }

        $roles = $user->getRoles();

        // Build permission slugs from user's roles
        $permissions = [];
        foreach ($user->getRoleEntities() as $role) {
            foreach ($role->getPermissions() as $permission) {
                $permissions[] = $permission->getName();
            }
        }
        $permissions = array_values(array_unique($permissions));

        // Symfony role slugs (ROLE_ADMIN → admin, ROLE_USER → user)
        $roleSlugs = array_values(array_filter(array_map(
            fn(string $r) => strtolower(str_replace('ROLE_', '', $r)),
            $roles
        ), fn(string $r) => !in_array($r, ['user'], true)));

        return [
            'user' => [
                'id'       => $user->getId(),
                'name'     => $user->getName(),
                'email'    => $user->getEmail(),
                'avatar'   => $user->getAvatar(),
                'timezone' => $user->getTimezone(),
                'language' => $user->getLanguage(),
                'roles'    => $roles,
            ],
            'roles'       => $roleSlugs,
            'permissions' => $permissions,
        ];
    }

    private function resolveTenant(): array
    {
        if (!$this->tenantContext->has()) {
            return [];
        }

        $tenant = $this->tenantContext->get();

        return [
            'id' => $tenant->getId(),
            'name' => $tenant->getName(),
            'slug' => $tenant->getSlug(),
            'locale' => $tenant->getLocale(),
            'timezone' => $tenant->getTimezone(),
        ];
    }

    private function resolveFlash(): array
    {
        $session = $this->requestStack->getSession();

        return [
            'success' => $session->getFlashBag()->get('success')[0] ?? null,
            'error' => $session->getFlashBag()->get('error')[0] ?? null,
            'warning' => $session->getFlashBag()->get('warning')[0] ?? null,
            'info' => $session->getFlashBag()->get('info')[0] ?? null,
        ];
    }
}
