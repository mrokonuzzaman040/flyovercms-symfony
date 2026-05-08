<?php

namespace App\Controller\Dashboard;

use App\Entity\Lead;
use App\Tenant\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TenantContext $tenantContext,
    ) {}

    #[Route('', name: 'dashboard.index', methods: ['GET'])]
    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Dashboard/Index', [
            'stats' => $this->getStats(),
            'recentActivities' => [],
            'chartData' => $this->getChartData(),
            'topPerformers' => [],
            'statusConfig' => $this->getStatusConfig(),
        ]);
    }

    private function getStats(): array
    {
        $qb = fn() => $this->em->createQueryBuilder()->from(Lead::class, 'l');

        return [
            'total_leads' => (int) $qb()->select('COUNT(l.id)')->getQuery()->getSingleScalarResult(),
            'new_leads' => (int) $qb()->select('COUNT(l.id)')->where('l.status = :s')->setParameter('s', 'new')->getQuery()->getSingleScalarResult(),
            'converted_leads' => (int) $qb()->select('COUNT(l.id)')->where('l.status = :s')->setParameter('s', 'converted')->getQuery()->getSingleScalarResult(),
            'follow_up_leads' => (int) $qb()->select('COUNT(l.id)')->where('l.status = :s')->setParameter('s', 'follow_up')->getQuery()->getSingleScalarResult(),
            'leads_this_month' => (int) $qb()->select('COUNT(l.id)')
                ->where('l.createdAt >= :start')->setParameter('start', new \DateTimeImmutable('first day of this month'))
                ->getQuery()->getSingleScalarResult(),
            'conversion_rate' => $this->getConversionRate(),
        ];
    }

    private function getConversionRate(): float
    {
        $total = (int) $this->em->createQueryBuilder()->select('COUNT(l.id)')->from(Lead::class, 'l')->getQuery()->getSingleScalarResult();
        if ($total === 0) return 0.0;
        $converted = (int) $this->em->createQueryBuilder()->select('COUNT(l.id)')->from(Lead::class, 'l')->where('l.status = :s')->setParameter('s', 'converted')->getQuery()->getSingleScalarResult();
        return round(($converted / $total) * 100, 2);
    }

    private function getChartData(): array
    {
        // Monthly leads for last 6 months
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = new \DateTimeImmutable("first day of -{$i} months");
            $end = $month->modify('last day of this month')->setTime(23, 59, 59);
            $count = (int) $this->em->createQueryBuilder()
                ->select('COUNT(l.id)')->from(Lead::class, 'l')
                ->where('l.createdAt >= :start AND l.createdAt <= :end')
                ->setParameter('start', $month->setTime(0, 0, 0))
                ->setParameter('end', $end)
                ->getQuery()->getSingleScalarResult();
            $monthlyData[$month->format('Y-m')] = $count;
        }

        return ['monthlyData6M' => $monthlyData, 'monthlyData12M' => [], 'dailyData7D' => [], 'dailyData30D' => []];
    }

    private function getStatusConfig(): array
    {
        return [
            'new' => ['title' => 'New', 'color' => '#6366f1'],
            'contacted' => ['title' => 'Contacted', 'color' => '#8b5cf6'],
            'qualified' => ['title' => 'Qualified', 'color' => '#f59e0b'],
            'follow_up' => ['title' => 'Follow Up', 'color' => '#3b82f6'],
            'converted' => ['title' => 'Converted', 'color' => '#10b981'],
            'closed' => ['title' => 'Closed', 'color' => '#6b7280'],
            'cancelled' => ['title' => 'Cancelled', 'color' => '#ef4444'],
        ];
    }
}
