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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\SmsVerificationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use OTPHP\TOTP;

class AuthController extends AbstractController
{
    #[Route('/signup', name: 'app_signup')]
    public function signup(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        HttpClientInterface $httpClient,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // -- Vérification reCAPTCHA v2 --
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            $secretKey = $_ENV['RECAPTCHA_SECRET_KEY'] ?? 'dummy';
            
            $response = $httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret'   => $secretKey,
                    'response' => $recaptchaResponse
                ]
            ]);
            $content = $response->toArray(false);

            if (empty($content['success'])) {
                $this->addFlash('error', 'Veuillez valider le reCAPTCHA.');
                return $this->render('auth/signup.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            );
            $user->setMotDePasse($hashedPassword);
            $user->setEtatCompte('INACTIF');
            $user->setIsVerified(false);
            $user->setIsPhoneVerified(false);
            $user->setDateCreation(new \DateTime());

            $em->persist($user);
            $em->flush();

            // -- Envoi de l'email de vérification --
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                (string) $user->getUserId(),
                $user->getEmail(),
                ['id' => $user->getUserId()]
            );

            $email = (new TemplatedEmail())
                ->from('agriconnect3a6@gmail.com')
                ->to($user->getEmail())
                ->subject('AgriConnect - Confirmation de votre compte')
                ->htmlTemplate('auth/confirmation_email.html.twig')
                ->context([
                    'signedUrl' => $signatureComponents->getSignedUrl(),
                    'user' => $user
                ]);

            $mailer->send($email);

            // Pas de redirection. On affiche la page avec le message de succès.
            return $this->render('auth/signup.html.twig', [
                'success' => true,
                'email'   => $user->getEmail(),
                'form'    => null, // Plus besoin du formulaire
            ]);
        }

        return $this->render('auth/signup.html.twig', [
            'form'    => $form->createView(),
            'success' => false,
        ]);
    }

    #[Route('/signin', name: 'app_signin')]
    public function signin(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        UserPasswordHasherInterface $passwordHasher,
        TokenStorageInterface $tokenStorage
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

                // --- Injection du Token Security Symfony pour satisfaire le Web Profiler ---
                $token = new UsernamePasswordToken($admin, 'main', $admin->getRoles());
                $tokenStorage->setToken($token);
                $session->set('_security_main', serialize($token));

                $this->addFlash('success', '✅ Connexion admin réussie ! Bienvenue ' . $admin->getPrenom());
                return $this->redirectToRoute('admin_dashboard');
            }

            // Sinon vérifier utilisateur
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user && $passwordHasher->isPasswordValid($user, $password)) {
                if ($user->getEtatCompte() !== 'ACTIF') {
                    $this->addFlash('error', "❌ Adresse ou mot de passe incorrect.");
                    return $this->redirectToRoute('app_signin');
                }

                // --- INTERCEPTION 2FA ---
                if ($user->getTotpSecret()) {
                    $session->set('pending_login_user_id', $user->getUserId());
                    return $this->redirectToRoute('app_2fa_challenge');
                }

                $session->set('user_id',   $user->getUserId());
                $session->set('user_name', $user->getNom());
                $session->set('user_role', $user->getRole());
                $session->set('user_type', 'USER');

                // --- Injection du Token Security Symfony pour satisfaire le Web Profiler ---
                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $tokenStorage->setToken($token);
                $session->set('_security_main', serialize($token));

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

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_signup');
        }

        $user = $em->getRepository(User::class)->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_signup');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation($request->getUri(), (string) $user->getUserId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_signup');
        }

        $user->setIsVerified(true);
        $em->flush();

        // Stocker en session pour l'étape 2
        $session->set('setup_phone_user_id', $user->getUserId());

        $this->addFlash('success', '✅ Votre adresse e-mail a bien été vérifiée. Passez à l\'étape 2.');
        return $this->redirectToRoute('app_setup_phone');
    }

    #[Route('/signup/phone', name: 'app_setup_phone')]
    public function setupPhone(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        $userId = $session->get('setup_phone_user_id');
        if (!$userId) {
            $this->addFlash('error', 'Veuillez d\'abord valider votre e-mail ou vous connecter.');
            return $this->redirectToRoute('app_signin');
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->redirectToRoute('app_signin');
        }

        // Si la requête est en POST, on sauvegarde le numéro de téléphone et on active le compte
        // (Note: La vérification SMS via API est gérée en frontend, le frontend appelle cette soumission que lors du succès)
        if ($request->isMethod('POST')) {
            $phone = $request->request->get('phone');
            if ($phone && preg_match('/^[0-9]{8}$/', $phone)) {
                $user->setTelephone('+216' . $phone);
                $user->setIsPhoneVerified(true);
                $user->setEtatCompte('ACTIF');
                $em->flush();

                $session->remove('setup_phone_user_id');
                $this->addFlash('success', '✅ Votre compte est totalement actif ! Vous pouvez vous connecter.');
                return $this->redirectToRoute('app_signin');
            } else {
                $this->addFlash('error', 'Numéro de téléphone invalide.');
            }
        }

        return $this->render('auth/phone_setup.html.twig', [
            'user' => $user
        ]);
    }
    #[Route('/profile/2fa/setup', name: 'app_2fa_setup')]
    public function setup2FA(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        $userId = $session->get('user_id');
        if (!$userId) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_signin');
        }

        $user = $em->getRepository(User::class)->find($userId);
            if ($user->getTotpSecret()) {
                $this->addFlash('info', 'La 2FA est déjà activée pour votre compte.');
                return $this->redirectToRoute('user_profil'); // Assuming their route is 'user_profil'
            }

        // Generate a new TOTP secret if not present in session for the setup
        $secret = $session->get('pending_2fa_secret');
        if (!$secret) {
            // Utilisation correcte de spomky-labs/otphp 11.x :
            $totp = TOTP::generate();
            $secret = $totp->getSecret();
            $session->set('pending_2fa_secret', $secret);
        } else {
            $totp = TOTP::createFromSecret($secret);
        }
        
        $totp->setLabel($user->getEmail());
        $totp->setIssuer('AgriConnect');
        
        $qrCodeUri = $totp->getProvisioningUri();

        // Generate QR Code image Base64
        $qrCode = new \Endroid\QrCode\QrCode(
            data: $qrCodeUri,
            size: 300,
            margin: 10
        );
        
        $writer = new \Endroid\QrCode\Writer\SvgWriter();
        $result = $writer->write($qrCode);
        
        $qrCodeBase64 = $result->getDataUri();

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');
            if ($totp->verify($code)) {
                $user->setTotpSecret($secret);
                $em->flush();
                $session->remove('pending_2fa_secret');
                $this->addFlash('success', '🛡️ Sécurité 2FA (Google Authenticator) activée avec succès !');
                return $this->redirectToRoute('user_profil');
            } else {
                $this->addFlash('error', 'Code incorrect, veuillez réessayer.');
            }
        }

        return $this->render('auth/2fa_setup.html.twig', [
            'qrCodeImage' => $qrCodeBase64,
            'secret'      => $secret
        ]);
    }

    #[Route('/signin/2fa', name: 'app_2fa_challenge')]
    public function challenge2FA(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): Response {
        $userId = $session->get('pending_login_user_id');
        if (!$userId) {
            return $this->redirectToRoute('app_signin');
        }

        $user = $em->getRepository(User::class)->find($userId);

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');
            $totp = TOTP::createFromSecret($user->getTotpSecret());

            if ($totp->verify($code)) {
                // Validation réussie, on connecte définitivement l'utilisateur
                $session->remove('pending_login_user_id');

                $session->set('user_id',   $user->getUserId());
                $session->set('user_name', $user->getNom());
                $session->set('user_role', $user->getRole());
                $session->set('user_type', 'USER');

                $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                $tokenStorage->setToken($token);
                $session->set('_security_main', serialize($token));

                $this->addFlash('success', '✅ Connexion réussie et sécurisée ! Bienvenue ' . $user->getNom());

                return match ($user->getRole()) {
                    'FOURNISSEUR' => $this->redirectToRoute('app_produit_index'),
                    default       => $this->redirectToRoute('app_culture_index'),
                };
            } else {
                $this->addFlash('error', 'Code Google Authenticator incorrect.');
            }
        }

        return $this->render('auth/2fa_challenge.html.twig');
    }

// ── Route 1 : Envoyer le code SMS ────────────────────────────────
#[Route('/api/send-sms', name: 'api_send_sms', methods: ['POST'])]
public function sendSms(
    Request $request,
    SmsVerificationService $smsService
): JsonResponse {
    $data  = json_decode($request->getContent(), true);
    $phone = $data['phone'] ?? null;

    if (!$phone || !preg_match('/^\+216[0-9]{8}$/', $phone)) {
        return $this->json([
            'success' => false,
            'message' => 'Numéro invalide.'
        ], 400);
    }

    try {
        $smsService->sendCode($phone);
        return $this->json([
            'success' => true,
            'message' => 'Code envoyé avec succès.'
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi : ' . $e->getMessage()
        ], 500);
    }
}

// ── Route 2 : Vérifier le code SMS ───────────────────────────────
#[Route('/api/verify-sms', name: 'api_verify_sms', methods: ['POST'])]
public function verifySms(
    Request $request,
    SmsVerificationService $smsService
): JsonResponse {
    $data  = json_decode($request->getContent(), true);
    $phone = $data['phone'] ?? null;
    $code  = $data['code']  ?? null;

    if (!$phone || !$code) {
        return $this->json([
            'success' => false,
            'message' => 'Données manquantes.'
        ], 400);
    }

    $valid = $smsService->verifyCode($phone, $code);

    return $this->json([
        'success' => $valid,
        'message' => $valid ? 'Numéro vérifié !' : 'Code incorrect ou expiré.'
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
