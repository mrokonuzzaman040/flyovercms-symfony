<?php

namespace App\Controller\FlyoverBd;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/flyover-bd')]
class FlyoverBdController extends AbstractController
{
    #[Route('', name: 'flyover-bd.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('FlyoverBd/Index', []);
    }

    #[Route('/bookings', name: 'flyover-bd.bookings', methods: ['GET'])]
    public function bookings(InertiaInterface $inertia): Response
    {
        return $inertia->render('FlyoverBd/Bookings', ['bookings' => []]);
    }

    #[Route('/contacts', name: 'flyover-bd.contacts', methods: ['GET'])]
    public function contacts(InertiaInterface $inertia): Response
    {
        return $inertia->render('FlyoverBd/Contacts', ['contacts' => []]);
    }

    #[Route('/inquiries', name: 'flyover-bd.inquiries', methods: ['GET'])]
    public function inquiries(InertiaInterface $inertia): Response
    {
        return $inertia->render('FlyoverBd/Inquiries', ['inquiries' => []]);
    }
}
