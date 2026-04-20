<?php

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Entity\User;
use App\Entity\Friendship;
use App\Repository\ChatMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chat')]
class ChatController extends AbstractController
{
    #[Route('/messages/{friendId}', name: 'app_chat_get_messages', methods: ['GET'])]
    public function getMessages(int $friendId, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $friend = $entityManager->getRepository(User::class)->find($friendId);

        if (!$friend) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }

        // Check if they are friends
        $friendship = $entityManager->getRepository(Friendship::class)->createQueryBuilder('f')
            ->where('((f.user = :u AND f.friend = :f) OR (f.user = :f AND f.friend = :u)) AND f.status = :s')
            ->setParameters(['u' => $currentUser, 'f' => $friend, 's' => 'accepted'])
            ->getQuery()
            ->getOneOrNullResult();

        if (!$friendship) {
            return $this->json(['success' => false, 'message' => 'Vous devez être amis pour discuter'], 403);
        }

        $messages = $entityManager->getRepository(ChatMessage::class)->createQueryBuilder('m')
            ->where('(m.sender = :u AND m.recipient = :f) OR (m.sender = :f AND m.recipient = :u)')
            ->setParameters(['u' => $currentUser, 'f' => $friend])
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($messages as $m) {
            $sender = $m->getSender();
            $senderImage = null;
            try {
                if ($sender->getProfile()) {
                    $senderImage = $sender->getProfile()->getImage();
                }
            } catch (\Exception $e) { }

            $data[] = [
                'id' => $m->getId(),
                'senderId' => $sender->getUserId(),
                'senderImage' => $senderImage,
                'content' => $m->getContent(),
                'date' => $m->getCreatedAt()->format('H:i'),
                'isOwn' => $sender->getUserId() === $currentUser->getUserId()
            ];
            
            // Mark as read if receiving
            if ($m->getRecipient()->getUserId() === $currentUser->getUserId() && !$m->isRead()) {
                $m->setIsRead(true);
            }
        }
        $entityManager->flush();

        return $this->json(['success' => true, 'messages' => $data]);
    }

    #[Route('/send/{friendId}', name: 'app_chat_send', methods: ['POST'])]
    public function sendMessage(int $friendId, Request $request, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $friend = $entityManager->getRepository(User::class)->find($friendId);

        $payload = json_decode($request->getContent(), true);
        $content = $payload['content'] ?? null;

        if (!$friend || !$content) {
            return $this->json(['success' => false, 'message' => 'Données invalides'], 400);
        }

        $message = new ChatMessage();
        $message->setSender($currentUser);
        $message->setRecipient($friend);
        $message->setContent($content);
        $entityManager->persist($message);
        $entityManager->flush();

        // Push to Mercure (Optional)
        try {
            $update = new Update(
                "https://agriconnect.com/chat/" . $friend->getUserId(),
                json_encode([
                    'type' => 'new_message',
                    'senderId' => $currentUser->getUserId(),
                    'senderName' => $currentUser->getNom(),
                    'senderImage' => ($currentUser->getProfile() && $currentUser->getProfile()->getImage()) ? $currentUser->getProfile()->getImage() : null,
                    'content' => $content,
                    'date' => $message->getCreatedAt()->format('H:i')
                ])
            );
            $hub->publish($update);
        } catch (\Exception $e) {
            // Silently fail
        }

        return $this->json([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'content' => $content,
                'date' => $message->getCreatedAt()->format('H:i')
            ]
        ]);
    }

    #[Route('/friends', name: 'app_chat_friends_list', methods: ['GET'])]
    public function getFriendsList(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var User $user */
        $user = $this->getUser();

        $friendships = $entityManager->getRepository(Friendship::class)->createQueryBuilder('f')
            ->where('((f.user = :u) OR (f.friend = :u)) AND f.status = :s')
            ->setParameters(['u' => $user, 's' => 'accepted'])
            ->getQuery()
            ->getResult();

        $friends = [];
        foreach ($friendships as $f) {
            $friend = ($f->getUser()->getUserId() === $user->getUserId()) ? $f->getFriend() : $f->getUser();
            
            // Check for unread messages
            $unreadCount = $entityManager->getRepository(ChatMessage::class)->count([
                'sender' => $friend,
                'recipient' => $user,
                'isRead' => false
            ]);

            $friendImage = null;
            try {
                if ($friend->getProfile()) {
                    $friendImage = $friend->getProfile()->getImage();
                }
            } catch (\Exception $e) { }

            $friends[] = [
                'id' => $friend->getUserId(),
                'name' => $friend->getNom(),
                'avatar' => strtoupper(substr($friend->getNom(), 0, 1)),
                'image' => $friendImage,
                'unreadCount' => $unreadCount
            ];
        }

        return $this->json(['success' => true, 'friends' => $friends]);
    }
}
