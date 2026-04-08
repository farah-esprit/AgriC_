<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\User;
use App\Form\LoginType;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/signup', name: 'app_signup')]
    public function signup(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            );
            $user->setMotDePasse($hashedPassword);
            $user->setEtatCompte('ACTIF');
            $user->setDateCreation(new \DateTime());

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', '✅ Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_signin');
        }

        return $this->render('auth/signup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/signin', name: 'app_signin')]
    public function signin(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Si déjà connecté
        if ($session->get('user_type') === 'ADMIN') {
            return $this->redirectToRoute('admin_dashboard');
        }
        if ($session->get('user_type') === 'USER') {
            return match ($session->get('user_role')) {
                'FOURNISSEUR' => $this->redirectToRoute('app_produit_index'),
                default       => $this->redirectToRoute('app_culture_index'),
            };
        }

        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data     = $form->getData();
            $email    = $data['email'];
            $password = $data['password'];

            // Vérifier admin d'abord
            $admin = $em->getRepository(Admin::class)->findOneBy(['email' => $email]);
            if ($admin && $passwordHasher->isPasswordValid($admin, $password)) {
                $session->set('admin_id',     $admin->getId());
                $session->set('admin_nom',    $admin->getNom());
                $session->set('admin_prenom', $admin->getPrenom());
                $session->set('admin_email',  $admin->getEmail());
                $session->set('user_type',    'ADMIN');

                $this->addFlash('success', '✅ Connexion admin réussie ! Bienvenue ' . $admin->getPrenom());
                return $this->redirectToRoute('admin_dashboard');
            }

            // Sinon vérifier utilisateur
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user && $passwordHasher->isPasswordValid($user, $password)) {
                if ($user->getEtatCompte() !== 'ACTIF') {
                    $this->addFlash('error', "❌ Votre compte est désactivé. Contactez l'administrateur.");
                    return $this->redirectToRoute('app_signin');
                }

                $session->set('user_id',   $user->getUserId());
                $session->set('user_name', $user->getNom());
                $session->set('user_role', $user->getRole());
                $session->set('user_type', 'USER');

                $this->addFlash('success', '✅ Connexion réussie ! Bienvenue ' . $user->getNom());

                return match ($user->getRole()) {
                    'FOURNISSEUR' => $this->redirectToRoute('app_produit_index'),
                    default       => $this->redirectToRoute('app_culture_index'),
                };
            }

            $this->addFlash('error', '❌ Email ou mot de passe incorrect.');
            return $this->redirectToRoute('app_signin');
        }

        return $this->render('auth/signin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->clear();
        $this->addFlash('success', '✅ Déconnexion réussie.');
        return $this->redirectToRoute('app_signin');
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function adminLogout(SessionInterface $session): Response
    {
        $session->clear();
        $this->addFlash('success', '✅ Déconnexion réussie.');
        return $this->redirectToRoute('app_signin');
    }
}
