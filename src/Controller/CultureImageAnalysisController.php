<?php

namespace App\Controller;

use App\Entity\Culture;
use App\Repository\CultureRepository;
use App\Service\PlantNetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/culture')]
class CultureImageAnalysisController extends AbstractController
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public function __construct(
        private readonly PlantNetService $plantNetService,
        private readonly CultureRepository $cultureRepository,
    ) {}

    /**
     * Analyse une image uploadée (multipart: image + option idCulture).
     */
    #[Route('/analyse-image', name: 'api_analyse_image', methods: ['POST'])]
    public function analyseImage(Request $request): JsonResponse
    {
        try {
            $uploadedFile = $request->files->get('image');
            if (!$uploadedFile instanceof UploadedFile) {
                return $this->json([
                    'success' => false,
                    'message' => 'Aucun fichier image uploadé.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $mimeError = $this->validateMime($uploadedFile->getMimeType());
            if ($mimeError !== null) {
                return $this->json(['success' => false, 'message' => $mimeError], Response::HTTP_BAD_REQUEST);
            }

            $tempPath = $this->storeTempUpload($uploadedFile);
            try {
                $resultat = $this->plantNetService->identifierPlante($tempPath);
            } finally {
                $this->unlinkIfExists($tempPath);
            }

            $culture = $this->resolveCultureFromRequest($request);
            $errorResponse = $this->jsonPlantNetError($resultat);
            if ($errorResponse !== null) {
                return $errorResponse;
            }

            if (!$resultat) {
                return $this->json(['success' => false, 'message' => 'Erreur lors de l\'analyse.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->json([
                'success' => true,
                'data' => $this->buildPayload($resultat, $culture),
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Analyse depuis une URL JSON: { "url": "...", "idCulture": 1 } (idCulture optionnel).
     */
    #[Route('/analyse-url', name: 'api_analyse_url', methods: ['POST'])]
    public function analyseUrl(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!is_array($data) || empty($data['url'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'URL image manquante.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $tempPath = sys_get_temp_dir() . '/' . uniqid('img_', true) . '.jpg';
            $imageContent = @file_get_contents($data['url']);
            if ($imageContent === false) {
                return $this->json([
                    'success' => false,
                    'message' => 'Impossible de télécharger l\'image depuis l\'URL.',
                ], Response::HTTP_BAD_REQUEST);
            }

            file_put_contents($tempPath, $imageContent);
            try {
                $resultat = $this->plantNetService->identifierPlante($tempPath);
            } finally {
                $this->unlinkIfExists($tempPath);
            }

            $culture = $this->resolveCultureFromPayload($data);
            $errorResponse = $this->jsonPlantNetError($resultat);
            if ($errorResponse !== null) {
                return $errorResponse;
            }

            if (!$resultat) {
                return $this->json(['success' => false, 'message' => 'Erreur lors de l\'analyse.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->json([
                'success' => true,
                'data' => $this->buildPayload($resultat, $culture),
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function validateMime(?string $mimeType): ?string
    {
        if (!in_array($mimeType, self::ALLOWED_MIMES, true)) {
            return 'Format d\'image non supporté. Utilisez JPEG, PNG, GIF ou WebP.';
        }

        return null;
    }

    private function storeTempUpload(UploadedFile $uploadedFile): string
    {
        $ext = $uploadedFile->guessExtension() ?: 'jpg';
        $tempPath = sys_get_temp_dir() . '/' . uniqid('img_', true) . '.' . $ext;
        $uploadedFile->move(dirname($tempPath), basename($tempPath));

        return $tempPath;
    }

    private function unlinkIfExists(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function resolveCultureFromRequest(Request $request): ?Culture
    {
        $raw = $request->request->get('idCulture');

        return $this->findCultureById($raw);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveCultureFromPayload(array $data): ?Culture
    {
        return $this->findCultureById($data['idCulture'] ?? null);
    }

    private function findCultureById(mixed $raw): ?Culture
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (!is_numeric($raw)) {
            return null;
        }

        return $this->cultureRepository->find((int) $raw);
    }

    /**
     * @param array<string, mixed>|null $resultat
     */
    private function jsonPlantNetError(?array $resultat): ?JsonResponse
    {
        if (!$resultat) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse de l\'image.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if (isset($resultat['erreur'])) {
            return $this->json([
                'success' => false,
                'message' => $resultat['erreur'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $resultat
     * @return array<string, mixed>
     */
    private function buildPayload(array $resultat, ?Culture $culture): array
    {
        $labels = [];
        foreach ($resultat['resultats'] ?? [] as $row) {
            if (!is_array($row)) {
                continue;
            }
            $labels[] = [
                'description' => ($row['nom_commun'] ?? $row['nom_scientifique'] ?? '') . '',
                'score' => isset($row['score']) ? (float) $row['score'] / 100.0 : 0.0,
            ];
        }

        return [
            'culture' => $culture ? [
                'idCulture' => $culture->getIdCulture(),
                'nom' => $culture->getNom(),
                'type' => $culture->getType(),
            ] : null,
            'plante_detectee' => $resultat['plante_detectee'],
            'nom_scientifique' => $resultat['nom_scientifique'],
            'confiance' => $resultat['confiance'],
            'resultats' => $resultat['resultats'] ?? [],
            'source' => $resultat['source'] ?? 'PlantNet',
            'cultureDetectee' => $resultat['plante_detectee'],
            'typeDetecte' => $culture?->getType(),
            'labels' => $labels,
        ];
    }
}
