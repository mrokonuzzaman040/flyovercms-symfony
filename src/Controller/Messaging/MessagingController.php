<?php

namespace App\Controller\Messaging;

use Rompetomp\InertiaBundle\Service\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    #[Route('/conversations', name: 'messaging.conversations.index', methods: ['GET'])]
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

    #[Route('/conversations', name: 'messaging.conversations.store', methods: ['POST'])]
    public function storeConversation(Request $request): JsonResponse
    {
        // TODO: create conversation
        return new JsonResponse(['success' => true, 'conversation' => ['id' => 1]]);
    }

    #[Route('/conversations/{id}/messages', name: 'messaging.conversations.messages', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function messages(int $id): JsonResponse
    {
        // TODO: return messages for conversation
        return new JsonResponse(['messages' => []]);
    }

    #[Route('/conversations/{id}/read', name: 'messaging.conversations.read', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function markConversationRead(int $id): JsonResponse
    {
        // TODO: mark conversation messages as read
        return new JsonResponse(['success' => true]);
    }

    #[Route('/messages', name: 'messaging.messages.store', methods: ['POST'])]
    public function storeMessage(Request $request): JsonResponse
    {
        // TODO: store message in conversation
        return new JsonResponse(['success' => true, 'message' => ['id' => 1]]);
    }

    #[Route('/messages/{id}/forward', name: 'messaging.messages.forward', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function forwardMessage(int $id, Request $request): JsonResponse
    {
        // TODO: forward message to another conversation
        return new JsonResponse(['success' => true]);
    }
}
