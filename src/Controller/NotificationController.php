<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    #[Route('/unread', name: 'app_notifications_unread', methods: ['GET'])]
    public function getUnread(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        $notifications = $entityManager->getRepository(Notification::class)->findBy(
            ['recipient' => $user],
            ['createdAt' => 'DESC'],
            10 // limit to 10 latest
        );

        $unreadCount = $entityManager->getRepository(Notification::class)->count([
            'recipient' => $user,
            'isRead' => false
        ]);

        $data = [];
        $now = new \DateTime();
        
        foreach ($notifications as $notification) {
            $diff = $now->diff($notification->getCreatedAt());
            $timeAgo = 'À l\'instant';
            
            if ($diff->d > 0) {
                $timeAgo = $diff->d === 1 ? 'Hier' : 'Il y a ' . $diff->d . ' jours';
            } elseif ($diff->h > 0) {
                $timeAgo = 'Il y a ' . $diff->h . 'h';
            } elseif ($diff->i > 0) {
                $timeAgo = 'Il y a ' . $diff->i . ' min';
            }

            $senderImage = null;
            try {
                if ($notification->getSender() && $notification->getSender()->getProfile()) {
                    $senderImage = $notification->getSender()->getProfile()->getImage();
                }
            } catch (\Exception $e) { }

            $data[] = [
                'id' => $notification->getId(),
                'message' => $notification->getMessage(),
                'url' => $notification->getUrl(),
                'isRead' => $notification->isRead(),
                'date' => $timeAgo,
                'senderAvatar' => $notification->getSender() ? strtoupper(substr($notification->getSender()->getNom(), 0, 1)) : 'S',
                'senderImage' => $senderImage
            ];
        }

        return $this->json([
            'success' => true,
            'unreadCount' => $unreadCount,
            'notifications' => $data
        ]);
    }

    #[Route('/mark-all-read', name: 'app_notifications_mark_all_read', methods: ['POST'])]
    public function markAllRead(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        $notifications = $entityManager->getRepository(Notification::class)->findBy([
            'recipient' => $user,
            'isRead' => false
        ]);

        foreach ($notifications as $notification) {
            $notification->setIsRead(true);
        }

        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/hide', name: 'app_notifications_hide', methods: ['DELETE'])]
    public function hideNotification(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        $notification = $entityManager->getRepository(Notification::class)->findOneBy([
            'id' => $id,
            'recipient' => $user
        ]);

        if ($notification) {
            $entityManager->remove($notification);
            $entityManager->flush();
            return $this->json(['success' => true]);
        }

        return $this->json(['success' => false, 'message' => 'Notification introuvable'], 404);
    }
}
