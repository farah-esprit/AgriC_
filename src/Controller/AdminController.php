<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\User;
use App\Form\AdminProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        if (!$session->get('admin_id')) {
            return $this->redirectToRoute('admin_login');
        }

        $users   = $em->getRepository(User::class)->findAll();
        $total   = count($users);
        $actifs  = count(array_filter($users, fn($u) => $u->getEtatCompte() === 'ACTIF'));
        $bloques = $total - $actifs;

        return $this->render('admin/dashboard.html.twig', [
            'stats'         => [5, 10, 8, 15, 20, 18],
            'total_users'   => $total,
            'active_users'  => $actifs,
            'blocked_users' => $bloques,
        ]);
    }

    #[Route('/admin/profil', name: 'admin_profile')]
    public function profile(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ): Response {
        if (!$session->get('admin_id')) {
            return $this->redirectToRoute('admin_login');
        }

        $admin = $em->getRepository(Admin::class)->find($session->get('admin_id'));

        if (!$admin) {
            $this->addFlash('error', 'Admin introuvable.');
            return $this->redirectToRoute('admin_login');
        }

        $form = $this->createForm(AdminProfileType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($admin, $plainPassword);
                $admin->setPassword($hashedPassword);
            }

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $photoFile */
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/admins',
                        $newFilename
                    );
                    $admin->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            $em->flush();

            $session->set('admin_nom',    $admin->getNom());
            $session->set('admin_prenom', $admin->getPrenom());

            $this->addFlash('success', '✅ Profil mis à jour avec succès.');
            return $this->redirectToRoute('admin_profile');
        }

        return $this->render('admin/profile.html.twig', [
            'form'  => $form->createView(),
            'admin' => $admin,
        ]);
    }

    #[Route('/admin/utilisateurs', name: 'admin_users')]
    public function users(
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        if (!$session->get('admin_id')) {
            return $this->redirectToRoute('admin_login');
        }

        $users = $em->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/utilisateurs/export-csv', name: 'admin_users_export_csv')]
    public function exportCsv(
        EntityManagerInterface $em,
        SessionInterface $session
    ): StreamedResponse {
        if (!$session->get('admin_id')) {
            // StreamedResponse ne peut pas faire de redirect — on redirige via Response classique
            return new StreamedResponse(function () {
                header('Location: ' . $this->generateUrl('admin_login'));
            });
        }

        $users = $em->getRepository(User::class)->findAll();

        $response = new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // En-têtes colonnes
            fputcsv($handle, ['Nom', 'Email', 'Téléphone', 'Rôle', 'État', 'Date création'], ';');

            // Données
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->getNom(),
                    $user->getEmail(),
                    $user->getTelephone() ?? 'N/A',
                    $user->getRole(),
                    $user->getEtatCompte(),
                    $user->getDateCreation()?->format('d/m/Y') ?? 'N/A',
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="utilisateurs_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/admin/utilisateur/{id}/toggle', name: 'admin_user_toggle')]
    public function toggleUser(
        int $id,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        if (!$session->get('admin_id')) {
            return $this->redirectToRoute('admin_login');
        }

        $user = $em->getRepository(User::class)->find($id);

        if ($user) {
            $newStatus = ($user->getEtatCompte() === 'ACTIF') ? 'BLOQUE' : 'ACTIF';
            $user->setEtatCompte($newStatus);
            $em->flush();

            $action = ($newStatus === 'BLOQUE') ? 'bloqué' : 'activé';
            $this->addFlash('success', "✅ Compte de {$user->getNom()} {$action} avec succès.");
        } else {
            $this->addFlash('error', '❌ Votre compte est désactivé pour le moment .');
        }

        return $this->redirectToRoute('admin_users');
    }
}
