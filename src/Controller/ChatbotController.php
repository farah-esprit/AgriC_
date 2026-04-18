<?php

namespace App\Controller;

use App\Service\StockChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    #[Route('/api/chatbot/stock', name: 'api_chatbot_stock', methods: ['POST'])]
    public function query(Request $request, StockChatbotService $chatbotService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (empty($message)) {
            return new JsonResponse(['error' => 'Message is required'], 400);
        }

        $reply = $chatbotService->getReply($message);

        return new JsonResponse($reply);
    }
}
