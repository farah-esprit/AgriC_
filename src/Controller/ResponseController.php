<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\Response;
use App\Entity\Thread;
use App\Entity\User;
use App\Repository\ResponseLikeRepository;
use App\Repository\ResponseRepository;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GeminiService;

#[Route('/response')]
class ResponseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ResponseRepository $responseRepository;
    private ThreadRepository $threadRepository;
    private ResponseLikeRepository $responseLikeRepository;
    private GeminiService $geminiService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ResponseRepository $responseRepository,
        ThreadRepository $threadRepository,
        ResponseLikeRepository $responseLikeRepository,
        GeminiService $geminiService
    ) {
        $this->entityManager = $entityManager;
        $this->responseRepository = $responseRepository;
        $this->threadRepository = $threadRepository;
        $this->responseLikeRepository = $responseLikeRepository;
        $this->geminiService = $geminiService;
    }

    /**
     * List all responses
     */
    #[Route('/', name: 'response_index', methods: ['GET'])]
    public function index(): HttpResponse
    {
        $responses = $this->responseRepository->findAll();

        return $this->render('response/index.html.twig', [
            'responses' => $responses,
        ]);
    }

    /**
     * Show a single response
     */
    #[Route('/{id}', name: 'response_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): HttpResponse
    {
        $response = $this->responseRepository->find($id);

        if (!$response) {
            throw $this->createNotFoundException('Response not found');
        }

        return $this->render('response/show.html.twig', [
            'response' => $response,
        ]);
    }

    /**
     * Edit a response
     */
    #[Route('/{id}/edit', name: 'response_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request): HttpResponse
    {
        $response = $this->responseRepository->find($id);

        if (!$response) {
            throw $this->createNotFoundException('Response not found');
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getUserId() !== $response->getUser()->getUserId()) {
            throw $this->createAccessDeniedException('You can only edit your own responses');
        }

        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');

            // Validation
            $errors = $this->validateResponseInput($content);
            
            // Check for moderation
            if (empty($errors) && !$this->geminiService->isContentSafe($content)) {
                $user = $this->getUser();
                if ($user instanceof User) {
                    $user->setStrikes($user->getStrikes() + 1);
                    if ($user->getStrikes() >= 5) {
                        $user->setEtatCompte('Banni');
                    }
                    
                    $notification = new Notification();
                    $notification->setRecipient($user);
                    $notification->setType('danger');
                    $notification->setMessage("⚠️ Alerte Sécurité : Votre modification de réponse a été bloquée pour contenu inapproprié. Sanction appliquée (+1 point).");
                    $this->entityManager->persist($notification);
                }
                
                $this->entityManager->flush();
                $errors['content'] = '❌ Votre modification contient des propos inappropriés détectés par notre système de sécurité.';
            }

            if (!empty($errors)) {
                return $this->render('response/edit.html.twig', [
                    'response' => $response,
                    'errors' => $errors,
                ]);
            }

            // Update response
            $response->setContent($content);

            $this->entityManager->flush();

            $this->addFlash('success', 'Response updated successfully!');

            return $this->redirectToRoute('thread_show', ['id' => $response->getThread()->getId()]);
        }

        return $this->render('response/edit.html.twig', [
            'response' => $response,
        ]);
    }

    /**
     * Delete a response
     */
    #[Route('/{id}/delete', name: 'response_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request): HttpResponse
    {
        $response = $this->responseRepository->find($id);

        if (!$response) {
            throw $this->createNotFoundException('Response not found');
        }

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getUserId() !== $response->getUser()->getUserId()) {
            throw $this->createAccessDeniedException('You can only delete your own responses');
        }

        if (!$this->isCsrfTokenValid('delete_response' . $response->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $threadId = $response->getThread()->getId();

        // Delete ResponseLikes
        $responseLikes = $this->entityManager->getRepository(\App\Entity\ResponseLike::class)->findBy(['response' => $response]);
        foreach ($responseLikes as $like) {
            $this->entityManager->remove($like);
        }

        // Delete response
        $this->entityManager->remove($response);
        $this->entityManager->flush();

        $this->addFlash('success', 'Response deleted successfully!');

        return $this->redirectToRoute('thread_show', ['id' => $threadId]);
    }

    /**
     * Like a response
     */
    #[Route('/{id}/like', name: 'response_like', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function likeResponse(int $id, Request $request): HttpResponse
    {
        $response = $this->responseRepository->find($id);

        if (!$response) {
            throw $this->createNotFoundException('Response not found');
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('User must be logged in to like a response');
        }

        if (!$this->isCsrfTokenValid('like_response' . $response->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $threadId = $response->getThread()->getId();
        $existingLike = $this->responseLikeRepository->findLike($currentUser, $response);

        if ($existingLike) {
            $this->entityManager->remove($existingLike);
            $response->setLikeCount(max(0, ($response->getLikeCount() ?? 0) - 1));
        } else {
            $responseLike = new \App\Entity\ResponseLike();
            $responseLike->setUser($currentUser);
            $responseLike->setResponse($response);
            $responseLike->setCreatedAt(new \DateTime());
            $this->entityManager->persist($responseLike);

            $response->setLikeCount(($response->getLikeCount() ?? 0) + 1);

            // Create Notification
            if ($response->getUser() && $response->getUser()->getUserId() !== $currentUser->getUserId()) {
                $notification = new Notification();
                $notification->setRecipient($response->getUser());
                $notification->setSender($currentUser);
                $notification->setType('RESPONSE_LIKE');
                $notification->setMessage(sprintf('%s a aimé votre réponse.', $currentUser->getNom()));
                $notification->setUrl('/thread/' . $threadId);
                $this->entityManager->persist($notification);
            }
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('thread_show', ['id' => $threadId]);
    }

    /**
     * Validate response input (controle de saisie)
     */
    private function validateResponseInput(?string $content): array
    {
        $errors = [];

        // Content validation
        if (empty($content)) {
            $errors['content'] = 'Response content is required';
        } elseif (strlen($content) < 3) {
            $errors['content'] = 'Response must be at least 3 characters';
        } elseif (strlen($content) > 2000) {
            $errors['content'] = 'Response cannot exceed 2000 characters';
        }

        return $errors;
    }
}
