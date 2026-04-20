<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\StoryLike;
use App\Entity\StoryComment;
use App\Entity\StoryView;
use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/story')]
class StoryController extends AbstractController
{
    #[Route('/add', name: 'app_story_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        $file = $request->files->get('story_media');

        if ($file) {
            $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/stories';
            if (!is_dir($uploadsDirectory)) {
                mkdir($uploadsDirectory, 0755, true);
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
            $newFilename = sprintf('%s_%s.%s', $safeName, uniqid(), $file->guessExtension());
            $file->move($uploadsDirectory, $newFilename);

            $story = new Story();
            $story->setUser($user);
            $story->setMediaUrl($newFilename);
            $story->setCreatedAt(new \DateTime());

            $entityManager->persist($story);
            $entityManager->flush();

            $this->addFlash('success', 'Votre story a été ajoutée !');
        } else {
            $this->addFlash('error', 'Aucun fichier sélectionné pour la story.');
        }

        // Redirect back to wherever they were
        return $this->redirect($request->headers->get('referer') ?? '/');
    }

    #[Route('/user/{id}', name: 'app_story_view', methods: ['GET'])]
    public function viewUserStories(int $id, EntityManagerInterface $entityManager): Response
    {
        // 24 hours ago
        $yesterday = new \DateTime('-24 hours');
        
        $stories = $entityManager->getRepository(Story::class)->createQueryBuilder('s')
            ->where('s.user = :userId')
            ->andWhere('s.createdAt >= :yesterday')
            ->setParameter('userId', $id)
            ->setParameter('yesterday', $yesterday)
            ->orderBy('s.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$stories || !$user) {
            $this->addFlash('error', 'Cette story n\'est plus disponible.');
            return $this->redirectToRoute('app_home');
        }

        // For each story, we also need to know if the current user liked it
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $storyData = [];

        foreach ($stories as $story) {
            $isLiked = false;
            if ($currentUser) {
                $like = $entityManager->getRepository(StoryLike::class)->findOneBy([
                    'story' => $story,
                    'user' => $currentUser
                ]);
                $isLiked = $like !== null;
            }

            $isAuthor = $currentUser && ($story->getUser()->getUserId() === $currentUser->getUserId());

            $storyData[] = [
                'entity' => $story,
                'likesCount' => $story->getLikes()->count(),
                'commentsCount' => $story->getComments()->count(),
                'viewsCount' => $story->getViews()->count(),
                'isLiked' => $isLiked,
                'isAuthor' => $isAuthor
            ];
        }

        return $this->render('story/view.html.twig', [
            'storiesData' => $storyData,
            'storyUser' => $user
        ]);
    }

    #[Route('/{id}/like', name: 'app_story_like', methods: ['POST'])]
    public function toggleLike(Story $story, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        $like = $entityManager->getRepository(StoryLike::class)->findOneBy([
            'story' => $story,
            'user' => $user
        ]);

        if ($like) {
            $entityManager->remove($like);
            $action = 'unliked';
        } else {
            $like = new StoryLike();
            $like->setStory($story);
            $like->setUser($user);
            $entityManager->persist($like);
            $action = 'liked';

            // Create Notification
            if ($story->getUser() && $story->getUser()->getUserId() !== $user->getUserId()) {
                $notification = new Notification();
                $notification->setRecipient($story->getUser());
                $notification->setSender($user);
                $notification->setType('STORY_LIKE');
                $notification->setMessage(sprintf('%s aime votre Story.', $user->getNom() ?? 'Un utilisateur'));
                $notification->setUrl('/story/user/' . $story->getUser()->getUserId()); // Link to view story
                $entityManager->persist($notification);
                $entityManager->flush();

                // Notify via Mercure (Optional)
                try {
                    $update = new Update(
                        "https://agriconnect.com/user/" . $story->getUser()->getUserId(),
                        json_encode(['type' => 'notification', 'message' => $notification->getMessage()])
                    );
                    $hub->publish($update);
                } catch (\Exception $e) {
                    // Silently fail
                }
            }
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'action' => $action,
            'likesCount' => $story->getLikes()->count()
        ]);
    }

    #[Route('/{id}/comments', name: 'app_story_comments', methods: ['GET'])]
    public function getComments(Story $story): Response
    {
        $comments = [];
        foreach ($story->getComments() as $comment) {
            $comments[] = [
                'id' => $comment->getId(),
                'userName' => $comment->getUser()->getNom(), // or appropriate name property
                'userAvatar' => strtoupper(substr($comment->getUser()->getNom(), 0, 1)),
                'content' => $comment->getContent(),
                'date' => $comment->getCreatedAt()->format('H:i')
            ];
        }

        return $this->json([
            'success' => true,
            'comments' => $comments
        ]);
    }

    #[Route('/{id}/comment', name: 'app_story_comment_add', methods: ['POST'])]
    public function addComment(Request $request, Story $story, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        $data = json_decode($request->getContent(), true);
        $content = isset($data['content']) ? trim($data['content']) : null;

        if (empty($content)) {
            return $this->json(['success' => false, 'message' => 'Le commentaire est vide'], 400);
        }

        $comment = new StoryComment();
        $comment->setStory($story);
        $comment->setUser($user);
        $comment->setContent($content);
        
        $entityManager->persist($comment);

        // Create Notification
        if ($story->getUser() && $story->getUser()->getUserId() !== $user->getUserId()) {
            $notification = new Notification();
            $notification->setRecipient($story->getUser());
            $notification->setSender($user);
            $notification->setType('STORY_COMMENT');
            $notification->setMessage(sprintf('%s a commenté votre Story.', $user->getNom() ?? 'Un utilisateur'));
            $notification->setUrl('/story/user/' . $story->getUser()->getUserId());
            $entityManager->persist($notification);
            $entityManager->flush();

            // Notify via Mercure (Optional)
            try {
                $update = new Update(
                    "https://agriconnect.com/user/" . $story->getUser()->getUserId(),
                    json_encode(['type' => 'notification', 'message' => $notification->getMessage()])
                );
                $hub->publish($update);
            } catch (\Exception $e) {
                // Silently fail
            }
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'comment' => [
                'id' => $comment->getId(),
                'userName' => $user->getNom(),
                'userAvatar' => strtoupper(substr($user->getNom(), 0, 1)),
                'content' => $comment->getContent(),
                'date' => $comment->getCreatedAt()->format('H:i')
            ],
            'commentsCount' => $story->getComments()->count()
        ]);
    }

    #[Route('/{id}/view', name: 'app_story_view_add', methods: ['POST'])]
    public function addView(Story $story, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Don't count author's own views
        if ($story->getUser() && $story->getUser()->getUserId() === $user->getUserId()) {
            return $this->json(['success' => true]);
        }

        $existingView = $entityManager->getRepository(StoryView::class)->findOneBy([
            'story' => $story,
            'user' => $user
        ]);

        if (!$existingView) {
            $view = new StoryView();
            $view->setStory($story);
            $view->setUser($user);
            $entityManager->persist($view);
            $entityManager->flush();
        }

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/views', name: 'app_story_views_list', methods: ['GET'])]
    public function getViews(Story $story): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $viewers = [];
        foreach ($story->getViews() as $view) {
            $u = $view->getUser();
            if ($u) {
                $viewers[] = [
                    'name' => $u->getNom(),
                    'avatar' => strtoupper(substr($u->getNom(), 0, 1)),
                    'date' => $view->getCreatedAt()->format('d/m/Y H:i')
                ];
            }
        }

        return $this->json([
            'success' => true,
            'viewers' => $viewers,
            'viewsCount' => count($viewers)
        ]);
    }

    #[Route('/{id}/delete', name: 'app_story_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $story = $entityManager->getRepository(Story::class)->find($id);

        if (!$story) {
            return $this->json(['success' => false, 'message' => 'Story not found'], 404);
        }

        /** @var User $user */
        $user = $this->getUser();
        
        if ($story->getUser()->getUserId() !== $user->getUserId()) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // The cascading on views, comments, and likes might not be configured correctly in MySQL
        // Manually delete related entities first just like in ThreadController
        $views = $entityManager->getRepository(StoryView::class)->findBy(['story' => $story]);
        foreach($views as $v) $entityManager->remove($v);

        $likes = $entityManager->getRepository(StoryLike::class)->findBy(['story' => $story]);
        foreach($likes as $l) $entityManager->remove($l);

        $comments = $entityManager->getRepository(StoryComment::class)->findBy(['story' => $story]);
        foreach($comments as $c) $entityManager->remove($c);

        $entityManager->remove($story);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}
