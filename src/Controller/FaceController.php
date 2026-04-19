<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FaceController extends AbstractController
{
    private string $pythonServiceUrl = 'http://127.0.0.1:8001';

    // ─────────────────────────────────────────────────────────────────────────
    // ENREGISTREMENT DU VISAGE (Page d'activation dans le profil)
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/profil/face/setup', name: 'face_setup')]
    public function setup(SessionInterface $session): Response
    {
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('app_signin');
        }

        return $this->render('face/setup.html.twig');
    }

    #[Route('/profil/face/save', name: 'face_save', methods: ['POST'])]
    public function saveFace(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em
    ): JsonResponse {
        $userId = $session->get('user_id');
        if (!$userId) {
            return new JsonResponse(['success' => false, 'message' => 'Non connecté.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $imageBase64 = $data['image'] ?? null;

        if (!$imageBase64) {
            return new JsonResponse(['success' => false, 'message' => 'Aucune image reçue.'], 400);
        }

        // Décoder le base64 et sauvegarder l'image de référence
        if (str_contains($imageBase64, ',')) {
            [, $imageBase64] = explode(',', $imageBase64);
        }

        $imageData = base64_decode($imageBase64);
        if ($imageData === false) {
            return new JsonResponse(['success' => false, 'message' => 'Image invalide.'], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/faces/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'face_' . $userId . '_' . time() . '.jpg';
        file_put_contents($uploadDir . $filename, $imageData);

        // Sauvegarder le chemin dans la base de données
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur introuvable.'], 404);
        }

        // Supprimer l'ancienne photo si elle existe
        $oldPath = $user->getFaceImagePath();
        if ($oldPath) {
            $oldFile = $this->getParameter('kernel.project_dir') . '/public/' . $oldPath;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $user->setFaceImagePath('uploads/faces/' . $filename);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => '✅ Votre visage a été enregistré avec succès !',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONNEXION PAR VISAGE
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/connexion/visage', name: 'face_login_page')]
    public function loginPage(): Response
    {
        return $this->render('face/login.html.twig');
    }

    #[Route('/connexion/visage/verify', name: 'face_login_verify', methods: ['POST'])]
    public function verifyFace(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email      = trim($data['email'] ?? '');
        $liveImage  = $data['image'] ?? null;

        if (!$email || !$liveImage) {
            return new JsonResponse(['success' => false, 'message' => 'Email et image requis.'], 400);
        }

        // Trouver l'utilisateur par email
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun compte trouvé avec cet email.'], 404);
        }

        // Vérifier que l'utilisateur a configuré la connexion faciale
        $faceImagePath = $user->getFaceImagePath();
        if (!$faceImagePath) {
            return new JsonResponse([
                'success' => false,
                'message' => 'La connexion faciale n\'est pas configurée pour ce compte.',
            ], 400);
        }

        // Vérifier que le compte est actif
        if ($user->getEtatCompte() !== 'ACTIF') {
            return new JsonResponse(['success' => false, 'message' => 'Votre compte est bloqué ou inactif.'], 403);
        }

        // Charger l'image de référence en base64
        $refImagePath = $this->getParameter('kernel.project_dir') . '/public/' . $faceImagePath;
        if (!file_exists($refImagePath)) {
            return new JsonResponse(['success' => false, 'message' => 'Image de référence introuvable.'], 500);
        }

        $refImageBase64 = base64_encode(file_get_contents($refImagePath));

        // Appeler le micro-service Python
        try {
            $client = HttpClient::create(['timeout' => 60]); // Timeout augmenté pour le 1er téléchargement
            $response = $client->request('POST', $this->pythonServiceUrl . '/verify', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'reference_image' => $refImageBase64,
                    'live_image'      => $liveImage,
                    'threshold'       => 0.6,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                // Le service a renvoyé une erreur (ex: 400 Bad Request car aucun visage détecté)
                $errorData = $response->toArray(false); // false pour ne pas jeter d'exception
                return new JsonResponse([
                    'success' => false,
                    'message' => '⚠️ ' . ($errorData['detail'] ?? 'Erreur lors de l\'analyse faciale.'),
                ], $statusCode);
            }

            $result = $response->toArray();

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => '❌ Le service Python est injoignable. Le téléchargement du modèle (580 Mo) est peut-être encore en cours.',
            ], 503);
        }

        if (!($result['verified'] ?? false)) {
            return new JsonResponse([
                'success' => false,
                'message' => '❌ Visage non reconnu. Essayez de nouveau dans une meilleure lumière.',
                'distance' => $result['distance'] ?? null,
            ]);
        }

        // ✅ Connexion réussie — créer la session
        $session->set('user_id',    $user->getUserId());
        $session->set('user_name',  $user->getNom());
        $session->set('user_email', $user->getEmail());
        $session->set('user_role',  $user->getRole());

        return new JsonResponse([
            'success'  => true,
            'message'  => '✅ Connexion réussie ! Bienvenue, ' . $user->getNom() . ' !',
            'redirect' => $this->generateUrl('app_home'),
        ]);
    }
}
