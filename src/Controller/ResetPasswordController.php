<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password_request')]
    public function request(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            $admin = null;
            if (!$user) {
                $admin = $em->getRepository(Admin::class)->findOneBy(['email' => $email]);
            }

            $account = $user ?? $admin;

            if ($account) {
                // Génération d'un token aléatoire
                $token = bin2hex(random_bytes(32));
                $account->setResetToken($token);
                $account->setResetTokenRequestedAt(new \DateTimeImmutable());
                $em->flush();

                // Envoi de l'email
                $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $emailMessage = (new Email())
                    ->from('agriconnect3a6@gmail.com')
                    ->to($email)
                    ->subject('AgriConnect - Réinitialisation de votre mot de passe')
                    ->html($this->renderView('reset_password/email.html.twig', [
                        'resetUrl' => $resetUrl,
                    ]));

                $mailer->send($emailMessage);

                $this->addFlash('success', 'Un e-mail de réinitialisation vous a été envoyé.');
                return $this->redirectToRoute('app_signin');
            }

            $this->addFlash('error', 'Aucun compte ne correspond à cette adresse e-mail.');
        }

        return $this->render('reset_password/request.html.twig');
    }

    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'app_reset_password')]
    public function reset(string $token, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);
        $admin = null;
        if (!$user) {
            $admin = $em->getRepository(Admin::class)->findOneBy(['resetToken' => $token]);
        }

        $account = $user ?? $admin;

        if (!$account) {
            $this->addFlash('error', 'Lien de réinitialisation invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Vérifier l'expiration (1 heure)
        $requestedAt = $account->getResetTokenRequestedAt();
        if ($requestedAt === null || $requestedAt->modify('+1 hour') < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Votre lien de réinitialisation a expiré. Veuillez refaire une demande.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            if (empty($password) || $password !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas ou sont vides.');
            } else {
                // Mise à jour du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($account, $password);
                
                if ($account instanceof User) {
                    $account->setMotDePasse($hashedPassword);
                } else {
                    $account->setPassword($hashedPassword);
                }

                // Invalidation du token
                $account->setResetToken(null);
                $account->setResetTokenRequestedAt(null);
                $em->flush();

                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_signin');
            }
        }

        return $this->render('reset_password/reset.html.twig', [
            'token' => $token
        ]);
    }
}
