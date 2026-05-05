<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Profil;
use App\Form\UserEditType;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AdminController extends AbstractController
{
    /**
     * Vérifie que l'utilisateur connecté est bien un admin.
     */
    private function isAdmin(SessionInterface $session): bool
    {
        return $session->get('user_id') && $session->get('user_role') === 'ADMIN';
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isAdmin($session)) {
            return $this->redirectToRoute('app_signin');
        }

        $users   = $em->getRepository(User::class)->findAll();
        // Exclure les admins du comptage des utilisateurs classiques
        $nonAdmins = array_filter($users, fn($u) => $u->getRole() !== 'ADMIN');
        $total   = count($nonAdmins);
        $actifs  = count(array_filter($nonAdmins, fn($u) => $u->getEtatCompte() === 'ACTIF'));
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
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if (!$this->isAdmin($session)) {
            return $this->redirectToRoute('app_signin');
        }

        /** @var User|null $admin */
        $admin = $em->getRepository(User::class)->find($session->get('user_id'));

        if (!$admin) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_signin');
        }

        $form = $this->createForm(UserEditType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();

            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($admin, $newPassword);
                $admin->setMotDePasse($hashedPassword);
            }

            $em->flush();

            $session->set('user_name', $admin->getNom());

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
        SessionInterface $session,
        ChartBuilderInterface $chartBuilder
    ): Response {
        if (!$this->isAdmin($session)) {
            return $this->redirectToRoute('app_signin');
        }

        // Récupérer seulement les non-admins
        $users = array_filter(
            $em->getRepository(User::class)->findAll(),
            fn($u) => $u->getRole() !== 'ADMIN'
        );
        $users = array_values($users);

        // --- Stats pour les graphiques ---
        $roleCount = ['AGRICULTEUR' => 0, 'FOURNISSEUR' => 0, 'Autre' => 0];
        foreach ($users as $u) {
            $r = strtoupper($u->getRole() ?? 'Autre');
            if (isset($roleCount[$r])) {
                $roleCount[$r]++;
            } else {
                $roleCount['Autre']++;
            }
        }

        // Inscriptions par mois (6 derniers mois)
        $monthLabels = [];
        $monthData   = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt = new \DateTime("-{$i} months");
            $monthLabels[] = $dt->format('M Y');
            $monthData[]   = 0;
        }
        foreach ($users as $u) {
            $dc = $u->getDateCreation();
            if (!$dc) continue;
            for ($i = 5; $i >= 0; $i--) {
                $dt = new \DateTime("-{$i} months");
                if ($dc->format('Y-m') === $dt->format('Y-m')) {
                    $monthData[5 - $i]++;
                    break;
                }
            }
        }

        // --- Graphique Pie: répartition rôles ---
        $pieChart = $chartBuilder->createChart(Chart::TYPE_PIE);
        $pieChart->setData([
            'labels' => ['👨‍🌾 Agriculteur', '🚚 Fournisseur', 'Autre'],
            'datasets' => [[
                'data'            => array_values($roleCount),
                'backgroundColor' => ['#22c55e', '#3b82f6', '#a855f7'],
                'borderColor'     => ['#16a34a', '#2563eb', '#9333ea'],
                'borderWidth'     => 2,
            ]],
        ]);
        $pieChart->setOptions([
            'plugins' => [
                'legend' => ['position' => 'bottom'],
                'tooltip' => ['enabled' => true],
            ],
        ]);

        // --- Graphique Line: inscriptions par mois ---
        $lineChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $lineChart->setData([
            'labels' => $monthLabels,
            'datasets' => [[
                'label'           => 'Nouvelles inscriptions',
                'data'            => $monthData,
                'borderColor'     => '#22c55e',
                'backgroundColor' => 'rgba(34,197,94,0.15)',
                'fill'            => true,
                'tension'         => 0.4,
                'pointBackgroundColor' => '#16a34a',
                'pointRadius'     => 5,
            ]],
        ]);
        $lineChart->setOptions([
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ]);

        return $this->render('admin/users.html.twig', [
            'users'        => $users,
            'pieChart'     => $pieChart,
            'lineChart'    => $lineChart,
            'totalUsers'   => count($users),
            'activeUsers'  => count(array_filter($users, fn($u) => $u->getEtatCompte() === 'ACTIF')),
            'blockedUsers' => count(array_filter($users, fn($u) => $u->getEtatCompte() !== 'ACTIF')),
        ]);
    }

    #[Route('/admin/utilisateur/{id}/historique', name: 'admin_user_history', methods: ['GET'])]
    public function userHistory(
        int $id,
        EntityManagerInterface $em,
        SessionInterface $session
    ): JsonResponse {
        if (!$this->isAdmin($session)) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], 404);
        }

        /** @var \Gedmo\Loggable\Entity\Repository\LogEntryRepository<object> $logRepo */
        $logRepo = $em->getRepository(LogEntry::class);
        /** @phpstan-ignore-next-line */
        $logs = $logRepo->getLogEntries($user);

        $profil = $em->getRepository(Profil::class)->findOneBy(['userId' => $id]);
        /** @phpstan-ignore-next-line */
        $profilLogs = $profil ? $logRepo->getLogEntries($profil) : [];

        $allLogs = array_merge($logs, $profilLogs);

        usort($allLogs, function ($a, $b) {
            return $b->getLoggedAt() <=> $a->getLoggedAt();
        });

        $history = [];
        foreach ($allLogs as $log) {
            $fieldLabels = [
                'nom'        => 'Nom',
                'email'      => 'Email',
                'telephone'  => 'Téléphone',
                'role'       => 'Rôle',
                'etatCompte' => 'État du compte',
                'bio'        => 'Bio',
                'image'      => 'Image de profil',
            ];
            $actionLabels = [
                'create' => 'Création',
                'update' => 'Modification',
                'remove' => 'Suppression',
            ];

            $data = $log->getData() ?? [];
            $fields = [];
            foreach ($data as $field => $value) {
                if (!isset($fieldLabels[$field]) && $field !== 'telephone') continue;

                $fields[] = [
                    'champ'    => $fieldLabels[$field],
                    'nouvelle' => $value,
                ];
            }

            if (empty($fields) && $log->getAction() !== 'create') {
                continue;
            }

            $loggedAt = $log->getLoggedAt();
            $history[] = [
                'date'    => $loggedAt ? $loggedAt->format('d/m/Y à H:i') : 'N/A',
                'action'  => $actionLabels[$log->getAction()] ?? $log->getAction(),
                'version' => $log->getVersion(),
                'champs'  => $fields,
            ];
        }

        return new JsonResponse(['user' => $user->getNom(), 'history' => $history]);
    }

    #[Route('/admin/utilisateurs/export-csv', name: 'admin_users_export_csv')]
    public function exportCsv(
        EntityManagerInterface $em,
        SessionInterface $session
    ): StreamedResponse {
        if (!$this->isAdmin($session)) {
            return new StreamedResponse(function () {
                header('Location: ' . $this->generateUrl('app_signin'));
            });
        }

        $users = array_filter(
            $em->getRepository(User::class)->findAll(),
            fn($u) => $u->getRole() !== 'ADMIN'
        );

        $response = new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // BOM UTF-8 pour Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Nom', 'Email', 'Téléphone', 'Rôle', 'État', 'Date création'], ';');

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
        if (!$this->isAdmin($session)) {
            return $this->redirectToRoute('app_signin');
        }

        $user = $em->getRepository(User::class)->find($id);

        if ($user) {
            $newStatus = ($user->getEtatCompte() === 'ACTIF') ? 'BLOQUE' : 'ACTIF';
            $user->setEtatCompte($newStatus);
            $em->flush();

            $action = ($newStatus === 'BLOQUE') ? 'bloqué' : 'activé';
            $this->addFlash('success', "✅ Compte de {$user->getNom()} {$action} avec succès.");
        } else {
            $this->addFlash('error', '❌ Utilisateur introuvable.');
        }

        return $this->redirectToRoute('admin_users');
    }
}
