<?php

namespace App\Controller\System;

use App\Entity\ActivityLog;
use App\Repository\ActivityLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/activity-log')]
class ActivityLogController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ActivityLogRepository $repo,
    ) {}

    #[Route('', name: 'activity-log.index', methods: ['GET'])]
    public function index(Request $request, InertiaInterface $inertia): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $qb = $this->em->createQueryBuilder()
            ->select('a')
            ->from(ActivityLog::class, 'a')
            ->where('a.tenantId = :tid')
            ->setParameter('tid', $user->getTenantId())
            ->orderBy('a.createdAt', 'DESC');

        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = 50;

        $total = (clone $qb)->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
        $logs  = $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage)->getQuery()->getResult();

        return $inertia->render('ActivityLog/Index', [
            'logs'       => array_map(fn(ActivityLog $a) => [
                'id'           => $a->getId(),
                'user_id'      => $a->getUserId(),
                'action'       => $a->getAction(),
                'subject_type' => $a->getSubjectType(),
                'subject_id'   => $a->getSubjectId(),
                'description'  => $a->getDescription(),
                'ip_address'   => $a->getIpAddress(),
                'created_at'   => $a->getCreatedAt()->format('Y-m-d H:i:s'),
            ], $logs),
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => (int) $total,
                'last_page'    => (int) ceil($total / $perPage),
            ],
        ]);
    }
}
