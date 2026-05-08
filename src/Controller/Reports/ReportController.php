<?php

namespace App\Controller\Reports;

use App\Entity\Lead;
use Doctrine\ORM\EntityManagerInterface;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reports')]
class ReportController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('', name: 'reports.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Reports/Index', [
            'summary' => $this->getSummary(),
        ]);
    }

    #[Route('/leads', name: 'reports.leads', methods: ['GET'])]
    public function leads(Request $request, InertiaInterface $inertia): Response
    {
        $qb = $this->em->createQueryBuilder()
            ->select('l.status, COUNT(l.id) as count')
            ->from(Lead::class, 'l')
            ->groupBy('l.status');

        $byStatus = $qb->getQuery()->getResult();

        return $inertia->render('Reports/Leads', [
            'by_status' => $byStatus,
            'filters' => $request->query->all(),
        ]);
    }

    #[Route('/generate', name: 'reports.generate', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        // TODO: async report generation via Symfony Messenger
        return new JsonResponse(['success' => true, 'message' => 'Report queued for generation.']);
    }

    #[Route('/download', name: 'reports.download', methods: ['GET'])]
    public function download(Request $request): Response
    {
        // TODO: stream generated report file
        return new Response('Report download placeholder', 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="report.csv"',
        ]);
    }

    private function getSummary(): array
    {
        $qb = $this->em->createQueryBuilder()->from(Lead::class, 'l');

        return [
            'total_leads' => (int) (clone $qb)->select('COUNT(l.id)')->getQuery()->getSingleScalarResult(),
            'new_leads' => (int) (clone $qb)->select('COUNT(l.id)')->where('l.status = :s')->setParameter('s', 'new')->getQuery()->getSingleScalarResult(),
            'converted_leads' => (int) (clone $qb)->select('COUNT(l.id)')->where('l.status = :s')->setParameter('s', 'converted')->getQuery()->getSingleScalarResult(),
        ];
    }
}
