<?php

namespace App\Controller;

use App\Entity\Thread;
use App\Entity\Response as ForumResponse;
use App\Entity\ThreadLike;
use App\Entity\User;
use App\Entity\Story;
use App\Entity\Notification;
use App\Form\ResponseType as ForumResponseType;
use App\Repository\ResponseLikeRepository;
use App\Repository\ResponseRepository;
use App\Repository\ThreadLikeRepository;
use App\Repository\ThreadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use App\Service\GeminiService;

#[Route('/thread')]
class ThreadController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ThreadRepository $threadRepository;
    private ResponseRepository $responseRepository;
    private ThreadLikeRepository $threadLikeRepository;
    private ResponseLikeRepository $responseLikeRepository;
    private GeminiService $geminiService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ThreadRepository $threadRepository,
        ResponseRepository $responseRepository,
        ThreadLikeRepository $threadLikeRepository,
        ResponseLikeRepository $responseLikeRepository,
        GeminiService $geminiService
    ) {
        $this->entityManager = $entityManager;
        $this->threadRepository = $threadRepository;
        $this->responseRepository = $responseRepository;
        $this->threadLikeRepository = $threadLikeRepository;
        $this->responseLikeRepository = $responseLikeRepository;
        $this->geminiService = $geminiService;
    }

    /**
     * List all threads (This is the Forum Homepage now!)
     */
    #[Route('/', name: 'thread_index', methods: ['GET'])]
    public function index(Request $request, ThreadRepository $threadRepository, EntityManagerInterface $entityManager): HttpResponse
    {
        $searchTerm = $request->query->get('search', '');
        $category = $request->query->get('category', '');

        // Build query based on search parameters
        $queryBuilder = $threadRepository->createQueryBuilder('t')
            ->leftJoin('t.user', 'u')
            ->leftJoin('t.responses', 'r')
            ->addSelect('u', 'r')
            ->orderBy('t.createdAt', 'DESC');

        // Apply title search
        if (!empty($searchTerm)) {
            $queryBuilder->andWhere('t.title LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        // Apply category filter
        if (!empty($category)) {
            $queryBuilder->andWhere('t.category = :category')
                ->setParameter('category', $category);
        }

        $threads = $queryBuilder->getQuery()->getResult();

        // Get unique categories for the filter dropdown
        $allThreads = $threadRepository->findAll();
        $categories = [];
        foreach ($allThreads as $thread) {
            if ($thread->getCategory() && !in_array($thread->getCategory(), $categories)) {
                $categories[] = $thread->getCategory();
            }
        }

        // Fetch active stories (last 24 hours) for the story bar
        $yesterday = new \DateTime('-24 hours');
        $activeStories = $entityManager->getRepository(Story::class)->createQueryBuilder('s')
            ->where('s.createdAt >= :yesterday')
            ->setParameter('yesterday', $yesterday)
            ->getQuery()
            ->getResult();

        // Group stories by user to avoid duplicates in the bar
        $activeStoriesUsers = [];
        foreach ($activeStories as $story) {
            if ($story->getUser()) {
                $activeStoriesUsers[$story->getUser()->getUserId()] = $story->getUser();
            }
        }

        $likedThreadIds = [];
        $currentUser = $this->getUser();
        if ($currentUser instanceof \App\Entity\User) {
            $likes = $entityManager->getRepository(\App\Entity\ThreadLike::class)->findBy(['user' => $currentUser]);
            foreach ($likes as $like) {
                if ($like->getThread()) {
                    $likedThreadIds[] = $like->getThread()->getId();
                }
            }
        }

        // ── FETCH TRENDING THREADS ──
        $mostLikedThreads = $threadRepository->findMostLiked(5);
        $mostCommentedThreads = $threadRepository->findMostCommented(5);

        return $this->render('thread/index.html.twig', [
            'threads' => $threads,
            'searchTerm' => $searchTerm,
            'categories' => $categories,
            'selectedCategory' => $category,
            'activeStoriesUsers' => $activeStoriesUsers,
            'likedThreadIds' => $likedThreadIds,
            'mostLikedThreads' => $mostLikedThreads,
            'mostCommentedThreads' => $mostCommentedThreads,
        ]);
    }

    /**
     * Show a single thread with its responses
     */
    #[Route('/{id}', name: 'thread_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): HttpResponse
    {
        $thread = $this->threadRepository->find($id);

        if (!$thread) {
            throw $this->createNotFoundException('Thread not found');
        }

        $responses = $this->responseRepository->findBy(['thread' => $id]);

        // Create response form
        $response = new ForumResponse();
        $responseForm = $this->createFormBuilder($response)
            ->add('content', TextareaType::class, [
                'label' => 'Votre réponse',
                'required' => true,
                'attr' => ['rows' => 6, 'placeholder' => 'Écrivez votre réponse ici...']
            ])
            ->getForm();

        $threadLiked = false;
        $responseLiked = [];
        $currentUser = $this->getUser();

        if ($currentUser instanceof User) {
            $threadLiked = $this->threadLikeRepository->existsLike($currentUser, $thread);

            foreach ($responses as $item) {
                $responseLiked[$item->getId()] = $this->responseLikeRepository->existsLike($currentUser, $item);
            }
        }

        return $this->render('thread/show.html.twig', [
            'thread' => $thread,
            'responses' => $responses,
            'responseForm' => $responseForm,
            'threadLiked' => $threadLiked,
            'responseLiked' => $responseLiked,
        ]);
    }

    /**
     * Create a new thread
     */
    #[Route('/new', name: 'thread_new', methods: ['GET', 'POST'])]
    public function new(Request $request): HttpResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $thread = new Thread();
        $form = $this->createFormBuilder($thread)
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'attr' => ['maxlength' => 255]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'required' => true,
                'attr' => ['rows' => 8]
            ])
            ->add('category', TextType::class, [
                'label' => 'Catégorie',
                'required' => false
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags (séparés par des virgules)',
                'required' => false
            ])
            ->add('attachments', FileType::class, [
                'label' => 'Images / Vidéos (optionnel)',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'attr' => ['accept' => 'image/*,video/*']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Publier le sujet'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check for moderation
            if (!$this->geminiService->isContentSafe($thread->getTitle() . ' ' . $thread->getContent())) {
                $user = $this->getUser();
                if ($user instanceof User) {
                    $user->setStrikes($user->getStrikes() + 1);
                    if ($user->getStrikes() >= 5) {
                        $user->setEtatCompte('Banni');
                    }
                    
                    $notification = new Notification();
                    $notification->setRecipient($user);
                    $notification->setType('danger');
                    $notification->setMessage("⚠️ Alerte Sécurité : Votre publication a été bloquée pour contenu inapproprié. Sanction appliquée (+1 point).");
                    $this->entityManager->persist($notification);
                }
                
                $this->entityManager->flush();
                
                $this->addFlash('error', "❌ Votre publication contient des propos inappropriés détectés par notre système de sécurité.");
                return $this->render('thread/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Set additional fields
            $thread->setCreatedAt(new \DateTime());
            $thread->setStatus('active');
            $thread->setLikeCount(0);

            // Assign the current logged-in user
            $currentUser = $this->getUser();
            if ($currentUser instanceof User) {
                $thread->setUser($currentUser);
            }

            $uploadedFiles = $form->get('attachments')->getData();
            if ($uploadedFiles) {
                $attachmentNames = [];
                $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
                if (!is_dir($uploadsDirectory)) {
                    mkdir($uploadsDirectory, 0755, true);
                }

                foreach ($uploadedFiles as $uploadedFile) {
                    if ($uploadedFile) {
                        $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
                        $newFilename = sprintf('%s_%s.%s', $safeName, uniqid(), $uploadedFile->guessExtension());
                        $uploadedFile->move($uploadsDirectory, $newFilename);
                        $attachmentNames[] = $newFilename;
                    }
                }

                if (!empty($attachmentNames)) {
                    $thread->setAttachments(implode(',', $attachmentNames));
                }
            }

            // Save to database
            $this->entityManager->persist($thread);

            // ── GAMIFICATION: XP POINTS EVALUATED BY AI ──
            $xpReward = $this->geminiService->calculateXpReward($thread->getTitle() . ' ' . $thread->getContent());
            
            if ($xpReward > 0) {
                if ($currentUser instanceof User) {
                    $currentUser->setXp($currentUser->getXp() + $xpReward);
                    $this->addFlash('success', sprintf("✨ Superbe contribution ! L'IA a évalué la qualité de votre sujet et vous a offert **+%d points d'expérience** !", $xpReward));
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre sujet a été créé avec succès !');

            return $this->redirectToRoute('thread_show', ['id' => $thread->getId()]);
        }

        return $this->render('thread/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Edit a thread
     */
    #[Route('/{id}/edit', name: 'thread_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request): HttpResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $thread = $this->threadRepository->find($id);

        if (!$thread) {
            throw $this->createNotFoundException('Thread not found');
        }

        // Check if user owns this thread or is admin
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getUserId() !== $thread->getUser()->getUserId()) {
            throw $this->createAccessDeniedException('You can only edit your own threads');
        }

        $form = $this->createFormBuilder($thread)
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'attr' => ['maxlength' => 255]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'required' => true,
                'attr' => ['rows' => 8]
            ])
            ->add('category', TextType::class, [
                'label' => 'Catégorie',
                'required' => false
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags (séparés par des virgules)',
                'required' => false
            ])
            ->add('attachments', FileType::class, [
                'label' => 'Images / Vidéos (optionnel)',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'attr' => ['accept' => 'image/*,video/*']
            ])
            ->add('status', TextType::class, [
                'label' => 'Statut',
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check for moderation
            if (!$this->geminiService->isContentSafe($thread->getTitle() . ' ' . $thread->getContent())) {
                $user = $this->getUser();
                if ($user instanceof User) {
                    $user->setStrikes($user->getStrikes() + 1);
                    if ($user->getStrikes() >= 5) {
                        $user->setEtatCompte('Banni');
                    }
                    
                    $notification = new Notification();
                    $notification->setRecipient($user);
                    $notification->setType('danger');
                    $notification->setMessage("⚠️ Alerte Sécurité : Votre modification a été bloquée pour contenu inapproprié. Sanction appliquée (+1 point).");
                    $this->entityManager->persist($notification);
                }
                
                $this->entityManager->flush();

                $this->addFlash('error', "❌ Vos modifications contiennent des propos inappropriés détectés par notre système de sécurité.");
                return $this->render('thread/edit.html.twig', [
                    'thread' => $thread,
                    'form' => $form->createView(),
                ]);
            }

            $uploadedFiles = $form->get('attachments')->getData();
            if ($uploadedFiles) {
                $attachmentNames = [];
                $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
                if (!is_dir($uploadsDirectory)) {
                    mkdir($uploadsDirectory, 0755, true);
                }

                foreach ($uploadedFiles as $uploadedFile) {
                    if ($uploadedFile) {
                        $originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
                        $newFilename = sprintf('%s_%s.%s', $safeName, uniqid(), $uploadedFile->guessExtension());
                        $uploadedFile->move($uploadsDirectory, $newFilename);
                        $attachmentNames[] = $newFilename;
                    }
                }

                if (!empty($attachmentNames)) {
                    $currentAttachments = $thread->getAttachments();
                    $existingAttachments = $currentAttachments ? explode(',', $currentAttachments) : [];
                    $thread->setAttachments(implode(',', array_merge($existingAttachments, $attachmentNames)));
                }
            }

            // Save to database
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre sujet a été mis à jour avec succès !');

            return $this->redirectToRoute('thread_show', ['id' => $thread->getId()]);
        }

        return $this->render('thread/edit.html.twig', [
            'thread' => $thread,
            'form' => $form,
        ]);
    }

    /**
     * Delete a thread
     */
    #[Route('/{id}/delete', name: 'thread_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, Request $request): HttpResponse
    {
        $thread = $this->threadRepository->find($id);

        if (!$thread) {
            throw $this->createNotFoundException('Thread not found');
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->getUserId() !== $thread->getUser()->getUserId()) {
            throw $this->createAccessDeniedException('You can only delete your own threads');
        }

        if (!$this->isCsrfTokenValid('delete' . $thread->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        // Delete ThreadLikes
        $threadLikes = $this->entityManager->getRepository(\App\Entity\ThreadLike::class)->findBy(['thread' => $thread]);
        foreach ($threadLikes as $like) {
            $this->entityManager->remove($like);
        }

        // Delete associated responses and their likes
        $responses = $this->responseRepository->findBy(['thread' => $id]);
        foreach ($responses as $response) {
            $responseLikes = $this->entityManager->getRepository(\App\Entity\ResponseLike::class)->findBy(['response' => $response]);
            foreach ($responseLikes as $like) {
                $this->entityManager->remove($like);
            }
            $this->entityManager->remove($response);
        }

        // Delete thread
        $this->entityManager->remove($thread);
        $this->entityManager->flush();

        $this->addFlash('success', 'Thread deleted successfully!');

        return $this->redirectToRoute('thread_index');
    }

    /**
     * Add a response to a thread
     */
    #[Route('/{id}/add-response', name: 'thread_add_response', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addResponse(int $id, Request $request, HubInterface $hub): HttpResponse
    {
        $thread = $this->threadRepository->find($id);

        if (!$thread) {
            throw $this->createNotFoundException('Thread not found');
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        $response = new ForumResponse();
        $responseForm = $this->createFormBuilder($response)
            ->add('content', TextareaType::class, [
                'label' => 'Votre réponse',
                'required' => true,
                'attr' => ['rows' => 6, 'placeholder' => 'Écrivez votre réponse ici...']
            ])
            ->getForm();

        $responseForm->handleRequest($request);

        if ($responseForm->isSubmitted() && $responseForm->isValid()) {
            // Check for moderation
            if (!$this->geminiService->isContentSafe($response->getContent())) {
                $user = $this->getUser();
                if ($user instanceof User) {
                    $user->setStrikes($user->getStrikes() + 1);
                    if ($user->getStrikes() >= 5) {
                        $user->setEtatCompte('Banni');
                    }
                    
                    $notification = new Notification();
                    $notification->setRecipient($user);
                    $notification->setType('danger');
                    $notification->setMessage("⚠️ Alerte Sécurité : Votre réponse a été bloquée pour contenu inapproprié. Sanction appliquée (+1 point).");
                    $this->entityManager->persist($notification);
                }
                
                $this->entityManager->flush();

                $this->addFlash('error', "❌ Votre réponse contient des propos inappropriés détectés par notre système de sécurité.");
                return $this->redirectToRoute('thread_show', ['id' => $id]);
            }

            $currentUser = $this->getUser();
            if ($currentUser instanceof User) {
                $response->setUser($currentUser);
            }

            $response->setThread($thread);
            $response->setCreatedAt(new \DateTime());
            $response->setLikeCount(0);

            $this->entityManager->persist($response);

            // Create notification for Thread Owner
            if ($thread->getUser() && $thread->getUser()->getUserId() !== $currentUser->getUserId()) {
                $notification = new Notification();
                $notification->setRecipient($thread->getUser());
                $notification->setSender($currentUser);
                $notification->setType('THREAD_COMMENT');
                $notification->setMessage(sprintf('%s a répondu à votre sujet "%s".', $currentUser->getNom(), $thread->getTitle()));
                $notification->setUrl('/thread/' . $thread->getId());
                $this->entityManager->persist($notification);
            }

            $this->entityManager->flush();

            // ── GAMIFICATION: XP POINTS FOR RESPONSE ──
            if ($currentUser instanceof User) {
                $xpReward = $this->geminiService->calculateXpReward($response->getContent());
                if ($xpReward > 0) {
                    $xpReward = min(2, $xpReward); // On limite à 2 XP max pour une réponse
                    $currentUser->setXp($currentUser->getXp() + $xpReward);
                    $this->entityManager->flush();
                    $this->addFlash('success', sprintf("✨ Réponse utile ! L'IA vous a attribué **+%d XP**.", $xpReward));
                }
            }

            // Notify via Mercure (Optional)
            if ($thread->getUser() && $thread->getUser()->getUserId() !== $currentUser->getUserId()) {
                try {
                    $update = new Update(
                        "https://agriconnect.com/user/" . $thread->getUser()->getUserId(),
                        json_encode(['type' => 'notification', 'message' => sprintf('%s a répondu à votre sujet "%s".', $currentUser->getNom(), $thread->getTitle())])
                    );
                    $hub->publish($update);
                } catch (\Exception $e) {
                    // Silently fail
                }
            }

            $this->addFlash('success', 'Votre réponse a été ajoutée avec succès !');

            return $this->redirectToRoute('thread_show', ['id' => $id]);
        }

        // If form is not valid, show the thread with errors
        $responses = $this->responseRepository->findBy(['thread' => $id]);
        $threadLiked = false;
        $responseLiked = [];
        $currentUser = $this->getUser();

        if ($currentUser instanceof User) {
            $threadLiked = $this->threadLikeRepository->existsLike($currentUser, $thread);
            foreach ($responses as $item) {
                $responseLiked[$item->getId()] = $this->responseLikeRepository->existsLike($currentUser, $item);
            }
        }

        return $this->render('thread/show.html.twig', [
            'thread' => $thread,
            'responses' => $responses,
            'responseForm' => $responseForm,
            'threadLiked' => $threadLiked,
            'responseLiked' => $responseLiked,
        ]);
    }

    /**
     * Like a thread
     */
    #[Route('/{id}/like', name: 'thread_like', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function likeThread(int $id, Request $request, HubInterface $hub): HttpResponse
    {
        $thread = $this->threadRepository->find($id);

        if (!$thread) {
            throw $this->createNotFoundException('Thread not found');
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('User must be logged in to like a thread');
        }

        if (!$this->isCsrfTokenValid('like' . $thread->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $existingLike = $this->threadLikeRepository->findLike($currentUser, $thread);
        if ($existingLike) {
            $this->entityManager->remove($existingLike);
            $likeCount = max(0, ($thread->getLikeCount() ?? 0) - 1);
            $thread->setLikeCount($likeCount);
        } else {
            $threadLike = new ThreadLike();
            $threadLike->setUser($currentUser);
            $threadLike->setThread($thread);
            $threadLike->setCreatedAt(new \DateTime());
            $this->entityManager->persist($threadLike);

            $thread->setLikeCount(($thread->getLikeCount() ?? 0) + 1);

            // Create notification for Thread Owner
            if ($thread->getUser() && $thread->getUser()->getUserId() !== $currentUser->getUserId()) {
                $notification = new Notification();
                $notification->setRecipient($thread->getUser());
                $notification->setSender($currentUser);
                $notification->setType('THREAD_LIKE');
                $notification->setMessage(sprintf('%s aime votre sujet "%s".', $currentUser->getNom(), $thread->getTitle()));
                $notification->setUrl('/thread/' . $thread->getId());
                $this->entityManager->persist($notification);
            }
        }

        $this->entityManager->flush();

        // Notify via Mercure (Optional)
        if (!isset($existingLike) || (!$existingLike && $thread->getUser() && $thread->getUser()->getUserId() !== $currentUser->getUserId())) {
            try {
                $update = new Update(
                    "https://agriconnect.com/user/" . $thread->getUser()->getUserId(),
                    json_encode(['type' => 'notification', 'message' => sprintf('%s aime votre sujet "%s".', $currentUser->getNom(), $thread->getTitle())])
                );
                $hub->publish($update);
            } catch (\Exception $e) {
                // Silently fail
            }
        }

        return $this->redirectToRoute('thread_show', ['id' => $id]);
    }

    /**
     * Share a thread to user profile
     */
    #[Route('/{id}/share', name: 'thread_share', methods: ['POST'])]
    public function share(int $id): HttpResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $thread = $this->threadRepository->find($id);
        if (!$thread) {
            return $this->json(['success' => false, 'message' => 'Sujet introuvable.'], 404);
        }

        /** @var User $user */
        $user = $this->getUser();
        
        if ($user->getSharedThreads()->contains($thread)) {
            $user->removeSharedThread($thread);
            $this->entityManager->flush();
            return $this->json(['success' => true, 'shared' => false, 'message' => 'Retiré de vos partages.']);
        }

        $user->addSharedThread($thread);
        $this->entityManager->flush();

        return $this->json(['success' => true, 'shared' => true, 'message' => 'Sujet partagé sur votre profil !']);
    }
}
