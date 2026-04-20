<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Entity\User;
use App\Entity\Notification;
use App\Repository\FriendshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/friend')]
class FriendshipController extends AbstractController
{
    #[Route('/request/{id}', name: 'app_friend_request', methods: ['POST'])]
    public function sendRequest(User $friend, EntityManagerInterface $entityManager, FriendshipRepository $repo, HubInterface $hub): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getUserId() === $friend->getUserId()) {
            return $this->json(['success' => false, 'message' => 'Vous ne pouvez pas vous ajouter vous-même'], 400);
        }

        // Check if a relationship already exists
        $existing = $entityManager->createQuery('SELECT f FROM App\Entity\Friendship f WHERE (f.user = :u AND f.friend = :f) OR (f.user = :f AND f.friend = :u)')
            ->setParameters(['u' => $user, 'f' => $friend])
            ->getOneOrNullResult();

        if ($existing) {
            return $this->json(['success' => false, 'message' => 'Une relation existe déjà'], 400);
        }

        $friendship = new Friendship();
        $friendship->setUser($user);
        $friendship->setFriend($friend);
        $friendship->setStatus('pending');

        $entityManager->persist($friendship);

        // Create notification for the friend
        $notification = new Notification();
        $notification->setRecipient($friend);
        $notification->setSender($user);
        $notification->setType('FRIEND_REQUEST');
        $notification->setMessage($user->getNom() . " vous a envoyé une demande d'ami.");
        $notification->setUrl("/profile/" . $user->getUserId());
        $notification->setIsRead(false);
        $entityManager->persist($notification);

        $entityManager->flush();

        // Notify via Mercure (Optional)
        try {
            $update = new Update(
                "https://agriconnect.com/user/" . $friend->getUserId(),
                json_encode(['type' => 'notification', 'message' => $notification->getMessage()])
            );
            $hub->publish($update);
        } catch (\Exception $e) {
            // Silently fail if Mercure is not available
        }

        return $this->json(['success' => true, 'message' => 'Demande envoyée']);
    }

    #[Route('/accept/{id}', name: 'app_friend_accept', methods: ['POST'])]
    public function acceptRequest(Friendship $friendship, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        if ($friendship->getFriend()->getUserId() !== $user->getUserId()) {
            return $this->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $friendship->setStatus('accepted');

        // Create notification for the requester
        $notification = new Notification();
        $notification->setRecipient($friendship->getUser());
        $notification->setSender($user);
        $notification->setType('FRIEND_ACCEPT');
        $notification->setMessage($user->getNom() . " a accepté votre demande d'ami.");
        $notification->setUrl("/profile/" . $user->getUserId());
        $notification->setIsRead(false);
        $entityManager->persist($notification);

        $entityManager->flush();

        // Notify via Mercure (Optional)
        try {
            $update = new Update(
                "https://agriconnect.com/user/" . $friendship->getUser()->getUserId(),
                json_encode(['type' => 'notification', 'message' => $notification->getMessage()])
            );
            $hub->publish($update);
        } catch (\Exception $e) {
            // Silently fail
        }

        return $this->json(['success' => true, 'message' => 'Demande acceptée']);
    }

    #[Route('/decline/{id}', name: 'app_friend_decline', methods: ['POST'])]
    public function declineRequest(Friendship $friendship, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        // Security check: either the sender or the receiver can cancel/decline
        if ($friendship->getFriend()->getUserId() !== $user->getUserId() && $friendship->getUser()->getUserId() !== $user->getUserId()) {
            return $this->json(['success' => false, 'message' => 'Non autorisé'], 403);
        }

        $entityManager->remove($friendship);
        $entityManager->flush();

        return $this->json(['success' => true, 'message' => 'Action effectuée']);
    }
}
