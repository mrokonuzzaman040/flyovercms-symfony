<?php

namespace App\Controller\System;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificationRepository $repo,
    ) {}

    #[Route('', name: 'notifications.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $notifications = $this->repo->findBy(
            ['userId' => $user->getId(), 'tenantId' => $user->getTenantId()],
            ['createdAt' => 'DESC'],
            50
        );

        return $inertia->render('Notifications/Index', [
            'notifications' => array_map(fn(Notification $n) => $this->serialize($n), $notifications),
        ]);
    }

    #[Route('/api', name: 'notifications.api', methods: ['GET'])]
    public function api(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $notifications = $this->repo->findBy(
            ['userId' => $user->getId(), 'tenantId' => $user->getTenantId(), 'isRead' => false],
            ['createdAt' => 'DESC'],
            20
        );

        return new JsonResponse([
            'notifications' => array_map(fn(Notification $n) => $this->serialize($n), $notifications),
            'unread_count'  => count($notifications),
        ]);
    }

    #[Route('/{id}/read', name: 'notifications.read', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function markRead(int $id): JsonResponse
    {
        $n = $this->findOr404($id);
        $n->markAsRead();
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/unread', name: 'notifications.mark-unread', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function markUnread(int $id): JsonResponse
    {
        $n = $this->findOr404($id);
        $n->markAsUnread();
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/read-all', name: 'notifications.read-all', methods: ['PATCH'])]
    public function markAllRead(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->em->createQueryBuilder()
            ->update(Notification::class, 'n')
            ->set('n.isRead', true)
            ->set('n.readAt', ':now')
            ->where('n.userId = :uid AND n.tenantId = :tid AND n.isRead = false')
            ->setParameters(['now' => new \DateTimeImmutable(), 'uid' => $user->getId(), 'tid' => $user->getTenantId()])
            ->getQuery()
            ->execute();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}', name: 'notifications.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id): JsonResponse
    {
        $this->em->remove($this->findOr404($id));
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }

    private function findOr404(int $id): Notification
    {
        /** @var User $user */
        $user = $this->getUser();
        $n = $this->repo->findOneBy(['id' => $id, 'userId' => $user->getId(), 'tenantId' => $user->getTenantId()]);
        if (!$n) throw $this->createNotFoundException();

        return $n;
    }

    private function serialize(Notification $n): array
    {
        return [
            'id'          => $n->getId(),
            'type'        => $n->getType(),
            'category'    => $n->getCategory(),
            'priority'    => $n->getPriority(),
            'title'       => $n->getTitle(),
            'message'     => $n->getMessage(),
            'action_url'  => $n->getActionUrl(),
            'action_text' => $n->getActionText(),
            'is_read'     => $n->isRead(),
            'read_at'     => $n->getReadAt()?->format('Y-m-d H:i:s'),
            'created_at'  => $n->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
