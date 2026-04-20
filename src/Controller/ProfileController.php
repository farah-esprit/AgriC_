<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Story;
use App\Repository\ThreadLikeRepository;
use App\Repository\ResponseLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Friendship;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(ThreadLikeRepository $threadLikeRepo, ResponseLikeRepository $responseLikeRepo, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        
        $yesterday = new \DateTime('-24 hours');
        $activeStories = $entityManager->getRepository(Story::class)->createQueryBuilder('s')
            ->where('s.createdAt >= :yesterday')
            ->setParameter('yesterday', $yesterday)
            ->getQuery()
            ->getResult();

        $activeStoriesUsers = [];
        foreach ($activeStories as $st) {
            if ($st->getUser()) {
                $activeStoriesUsers[$st->getUser()->getUserId()] = $st->getUser();
            }
        }

        // Calculate statistics
        $threadsCount = $user->getThreads()->count();
        $responsesCount = $user->getResponses()->count();

        // Likes received on user's threads and responses
        $likesReceivedOnThreads = 0;
        foreach ($user->getThreads() as $thread) {
            $likesReceivedOnThreads += (int) $thread->getLikeCount();
        }

        $likesReceivedOnResponses = 0;
        foreach ($user->getResponses() as $response) {
            $likesReceivedOnResponses += (int) $response->getLikeCount();
        }

        $totalLikesReceived = $likesReceivedOnThreads + $likesReceivedOnResponses;

        // Likes given by the user
        $threadsLikedCount = $threadLikeRepo->count(['user' => $user]);
        $responsesLikedCount = $responseLikeRepo->count(['user' => $user]);
        $totalLikesGiven = $threadsLikedCount + $responsesLikedCount;

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'threadsCount' => $threadsCount,
            'responsesCount' => $responsesCount,
            'totalLikesReceived' => $totalLikesReceived,
            'totalLikesGiven' => $totalLikesGiven,
            'activeStoriesUsers' => $activeStoriesUsers,
            'friendsCount' => $this->getFriendsCount($user, $entityManager),
        ]);
    }

    #[Route('/profile/{id}', name: 'app_profile_show')]
    public function show(User $targetUser, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($targetUser->getUserId() === $currentUser->getUserId()) {
            return $this->redirectToRoute('app_profile');
        }

        // Check friendship status
        $friendship = $entityManager->getRepository(Friendship::class)->createQueryBuilder('f')
            ->where('(f.user = :u AND f.friend = :f) OR (f.user = :f AND f.friend = :u)')
            ->setParameters(['u' => $currentUser, 'f' => $targetUser])
            ->getQuery()
            ->getOneOrNullResult();

        return $this->render('profile/show.html.twig', [
            'user' => $targetUser,
            'friendship' => $friendship,
            'isOwnProfile' => false
        ]);
    }

    private function getFriendsCount(User $user, EntityManagerInterface $em): int
    {
        return $em->getRepository(Friendship::class)->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('((f.user = :u) OR (f.friend = :u)) AND f.status = :s')
            ->setParameters(['u' => $user, 's' => 'accepted'])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
