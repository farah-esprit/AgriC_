<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectAction(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['email', 'profile']); 
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(
        Request $request, 
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage
    ) {
        $client = $clientRegistry->getClient('google');

        try {
            /** @var GoogleUser $googleUser */
            $googleUser = $client->fetchUser();

            $email = $googleUser->getEmail();
            $nom = $googleUser->getLastName();
            $prenom = $googleUser->getFirstName();

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                // L'utilisateur est nouveau, on sauvegarde ses infos dans la session et on le redirige
                $fullName = trim(($nom ?? '') . ' ' . ($prenom ?? 'Google User'));
                $session->set('google_new_user', [
                    'email' => $email,
                    'nom' => $fullName
                ]);

                return $this->redirectToRoute('app_google_setup_role');
            } else {
                if ($user->getEtatCompte() !== 'ACTIF') {
                    $this->addFlash('error', "❌ Votre compte est désactivé. Contactez l'administrateur.");
                    return $this->redirectToRoute('app_signin');
                }
                $this->addFlash('success', '✅ Connexion Google réussie ! Bienvenue ' . $user->getNom());
            }

            // Authentification manuelle en session (système actuel du projet)
            $session->set('user_id',   $user->getUserId());
            $session->set('user_name', $user->getNom());
            $session->set('user_role', $user->getRole());
            $session->set('user_type', 'USER');

            // --- Injection du Token pour satisfaire Symfony/Profiler ---
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $tokenStorage->setToken($token);
            $session->set('_security_main', serialize($token));

            return match ($user->getRole()) {
                'FOURNISSEUR' => $this->redirectToRoute('app_produit_index'),
                default       => $this->redirectToRoute('app_culture_index'),
            };

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur d\'authentification avec Google : ' . $e->getMessage());
            return $this->redirectToRoute('app_signin');
        }
    }

    #[Route('/connect/google/role', name: 'app_google_setup_role')]
    public function setupRoleAction(
        Request $request, 
        SessionInterface $session, 
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): Response {
        $googleData = $session->get('google_new_user');

        // Si la session n'existe pas, on redirige vers la connexion
        if (!$googleData) {
            return $this->redirectToRoute('app_signin');
        }

        if ($request->isMethod('POST')) {
            $role = $request->request->get('role');
            
            // Sécurité : éviter l'injection de rôles "Admin" etc.
            if (!in_array($role, ['AGRICULTEUR', 'FOURNISSEUR', 'EXPERT'])) {
                $this->addFlash('error', 'Rôle invalide.');
                return $this->redirectToRoute('app_google_setup_role');
            }

            // Création du compte avec les données Google + Rôle choisi
            $user = new User();
            $user->setEmail($googleData['email']);
            $user->setNom($googleData['nom']);
            $user->setRole($role);
            $user->setMotDePasse(bin2hex(random_bytes(16)));
            $user->setTelephone('00000000');

            $em->persist($user);
            $em->flush();

            // Nettoyer la session temporaire Google
            $session->remove('google_new_user');

            $this->addFlash('success', '✅ Bienvenue ! Votre compte a été créé avec le rôle ' . strtolower($role) . '.');

            // Authentification manuelle
            $session->set('user_id',   $user->getUserId());
            $session->set('user_name', $user->getNom());
            $session->set('user_role', $user->getRole());
            $session->set('user_type', 'USER');

            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $tokenStorage->setToken($token);
            $session->set('_security_main', serialize($token));

            return match ($user->getRole()) {
                'FOURNISSEUR' => $this->redirectToRoute('app_produit_index'),
                default       => $this->redirectToRoute('app_culture_index'),
            };
        }

        return $this->render('auth/google_role.html.twig', [
            'google_data' => $googleData
        ]);
    }
}
