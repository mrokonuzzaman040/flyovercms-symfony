<?php

namespace App\Controller\Messaging;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/messaging')]
class MessagingController extends AbstractController
{
    #[Route('', name: 'messaging.index', methods: ['GET'])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Messaging/Index', [
            'conversations' => [],
        ]);
    }

    #[Route('/conversations', name: 'messaging.conversations', methods: ['GET'])]
    public function conversations(InertiaInterface $inertia): Response
    {
        return $inertia->render('Messaging/Conversations/Index', ['conversations' => []]);
    }

    #[Route('/conversations/{id}', name: 'messaging.conversations.show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function conversation(int $id, InertiaInterface $inertia): Response
    {
        return $inertia->render('Messaging/Conversations/Show', [
            'conversation' => ['id' => $id],
            'messages' => [],
        ]);
    }
}
