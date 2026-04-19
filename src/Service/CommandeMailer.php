<?php

namespace App\Service;

use App\Entity\Commande;
use App\Repository\UserRepository;
use App\Service\GmailApiMailer;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Environment;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class CommandeMailer
{
    public function __construct(
        private GmailApiMailer $gmailApiMailer,
        private LoggerInterface $logger,
        private Environment $twig,
        private string $mailFromAddress,
        private string $mailFromName,
        private UserRepository $userRepository
    ) {}

    public function sendConfirmation(Commande $commande): void
    {
        $user = $commande->getUser();
        $recipientEmail = $user?->getEmail();
        $recipientName  = $user?->getNom() ?? 'Client';

        if (!$recipientEmail) {
            $this->logger->warning('Email confirmation non envoyé : utilisateur sans email', [
                'commande_id' => $commande->getIdCommande(),
            ]);
            return;
        }

        $html = $this->twig->render('emails/confirmation.html.twig', [
            'commande' => $commande,
            'user' => $user,
        ]);

        $email = (new Email())
            ->from(new Address($this->mailFromAddress, $this->mailFromName))
            ->to(new Address($recipientEmail, $recipientName))
            ->subject('Confirmation de votre commande — AgriC')
            ->html($html)
            ->text(strip_tags($html));

        $this->send($email, 'confirmation', $commande->getIdCommande());
    }

    public function sendStatutUpdate(Commande $commande): void
    {
        $user = $commande->getUser();

        if (!$user || !$user->getEmail()) {
            $this->logger->warning('Email statut non envoyé : utilisateur sans email', [
                'commande_id' => $commande->getIdCommande(),
            ]);
            return;
        }

        $html = $this->twig->render('emails/statut_update.html.twig', [
            'commande' => $commande,
            'user' => $user,
        ]);

        $email = (new Email())
            ->from(new Address($this->mailFromAddress, $this->mailFromName))
            ->to(new Address($user->getEmail(), $user->getNom() ?? 'Client'))
            ->subject('Mise à jour commande #' . $commande->getIdCommande())
            ->html($html)
            ->text(strip_tags($html));

        $this->send($email, 'statut_update', $commande->getIdCommande());
    }

    public function sendNewOrderNotificationToAdmins(Commande $commande): void
    {
        $admins = $this->userRepository->findBy(['role' => 'ADMIN']);

        if (empty($admins)) {
            $this->logger->warning('Aucun admin trouvé pour notification nouvelle commande', [
                'commande_id' => $commande->getIdCommande(),
            ]);
            return;
        }

        foreach ($admins as $admin) {
            if (!$admin->getEmail()) {
                continue;
            }

            $html = $this->twig->render('emails/new_order_admin.html.twig', [
                'commande' => $commande,
                'admin' => $admin,
            ]);

            $email = (new Email())
                ->from(new Address($this->mailFromAddress, $this->mailFromName))
                ->to(new Address($admin->getEmail(), $admin->getNom() ?? 'Admin'))
                ->subject('Nouvelle commande #' . $commande->getIdCommande() . ' — AgriC')
                ->html($html)
                ->text(strip_tags($html));

            $this->send($email, 'new_order_admin', $commande->getIdCommande());
        }
    }

    private function send(Email $email, string $type, mixed $id): void
    {
        try {
            $this->gmailApiMailer->sendEmail($email);

            $this->logger->info(sprintf('Email %s envoyé', $type), [
                'commande_id' => $id,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('Email %s échoué', $type), [
                'commande_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

