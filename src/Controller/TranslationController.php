<?php

namespace App\Controller;

use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TranslationController extends AbstractController
{
    private $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    #[Route('/translate', name: 'app_translate', methods: ['POST'])]
    public function translate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $text = $data['text'] ?? '';
        $targetLang = $data['targetLang'] ?? 'français';

        if (empty($text)) {
            return $this->json(['success' => false, 'message' => 'Texte vide'], 400);
        }

        try {
            $translatedText = $this->geminiService->translate($text, $targetLang);
            return $this->json([
                'success' => true,
                'translatedText' => $translatedText
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
