<?php

namespace App\Controller\Lead;

use App\Entity\Lead;
use App\Entity\User;
use App\Repository\LeadRepository;
use App\Service\Lead\LeadReadService;
use App\Service\Lead\LeadWriteService;
use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/leads')]
class LeadController extends AbstractController
{
    public function __construct(
        private readonly LeadReadService $readService,
        private readonly LeadWriteService $writeService,
        private readonly LeadRepository $leadRepository,
    ) {}

    #[Route('', name: 'leads.index', methods: ['GET'])]
    public function index(Request $request, InertiaInterface $inertia): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $inertia->render('Leads/Index', $this->readService->getIndexPageData($request));
    }

    #[Route('/create', name: 'leads.create', methods: ['GET'])]
    public function create(Request $request, InertiaInterface $inertia): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $inertia->render('Leads/Create', $this->readService->getCreatePageData($user));
    }

    #[Route('', name: 'leads.store', methods: ['POST'])]
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = $request->request->all();

        try {
            $lead = $this->writeService->create($data, $user);

            $request->getSession()->getFlashBag()->add('success', 'Lead created successfully.');

            return $this->redirectToRoute('leads.show', ['id' => $lead->getId()]);
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', 'Failed to create lead: ' . $e->getMessage());

            return $this->redirectToRoute('leads.create');
        }
    }

    #[Route('/{id}', name: 'leads.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, InertiaInterface $inertia): Response
    {
        $lead = $this->findLeadOr404($id);

        return $inertia->render('Leads/Show', $this->readService->getShowPageData($lead));
    }

    #[Route('/{id}/edit', name: 'leads.edit', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function edit(int $id, InertiaInterface $inertia): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $lead = $this->findLeadOr404($id);

        return $inertia->render('Leads/Edit', $this->readService->getEditPageData($user, $lead));
    }

    #[Route('/{id}', name: 'leads.update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $lead = $this->findLeadOr404($id);

        $this->writeService->update($lead, $request->request->all(), $user);

        $request->getSession()->getFlashBag()->add('success', 'Lead updated successfully.');

        return $this->redirectToRoute('leads.show', ['id' => $lead->getId()]);
    }

    #[Route('/{id}', name: 'leads.destroy', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function destroy(int $id, Request $request): RedirectResponse
    {
        $lead = $this->findLeadOr404($id);

        $this->writeService->delete($lead);

        $request->getSession()->getFlashBag()->add('success', 'Lead deleted successfully.');

        return $this->redirectToRoute('leads.index');
    }

    #[Route('/status/{status}', name: 'leads.status', methods: ['GET'])]
    public function byStatus(string $status, Request $request, InertiaInterface $inertia): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $inertia->render('Leads/Status', array_merge(
            $this->readService->getIndexPageData($request),
            ['status' => $status, 'follow_up_scope' => $request->query->get('follow_up_scope', 'all')]
        ));
    }

    #[Route('/{id}/status', name: 'leads.status.update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function updateStatus(int $id, Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $lead = $this->findLeadOr404($id);

        $this->writeService->update($lead, [
            'status' => $request->request->get('status'),
            'sub_status' => $request->request->get('sub_status'),
        ], $user);

        $request->getSession()->getFlashBag()->add('success', 'Lead status updated.');

        return $this->redirectToRoute('leads.show', ['id' => $lead->getId()]);
    }

    private function findLeadOr404(int $id): Lead
    {
        $lead = $this->leadRepository->find($id);

        if (!$lead) {
            throw $this->createNotFoundException("Lead #{$id} not found.");
        }

        return $lead;
    }
}
