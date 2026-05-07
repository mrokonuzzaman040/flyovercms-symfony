<?php

namespace App\Tenant;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Kernel subscriber that:
 * 1. Resolves tenant from request
 * 2. Enables Doctrine TenantFilter with resolved tenant_id
 */
class TenantSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TenantResolver $resolver,
        private readonly TenantContext $context,
        private readonly EntityManagerInterface $em,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->resolver->resolve($event->getRequest());

        if ($this->context->has()) {
            $filter = $this->em->getFilters()->enable('tenant_filter');
            $filter->setParameter('tenant_id', $this->context->getId(), 'integer');
        }
    }
}
