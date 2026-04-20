<?php

namespace App\Controller;

use App\Entity\Profil;
use App\Entity\User;
use App\Form\UserEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'user_profil')]
    public function profil(EntityManagerInterface $em, SessionInterface $session): Response
    {
        if (!$session->get('user_id')) {
            return $this->redirectToRoute('app_signin');
        }

        $user = $em->getRepository(User::class)->find($session->get('user_id'));

        if (!$user) {
            $session->clear();
            return $this->redirectToRoute('app_signin');
        }

        $profil = $em->getRepository(Profil::class)
            ->findOneBy(['user' => $user]) ?? new Profil();

        $stats = [
            'threads' => 0,
            'responses' => 0,
            'likes_received' => 0,
            'likes_given' => 0
        ];

        $canAccessForum = in_array($user->getRole(), ['AGRICULTEUR', 'EXPERT']);

        if ($canAccessForum) {
            $stats['threads'] = count($user->getThreads());
            $stats['responses'] = count($user->getResponses());

            foreach ($user->getThreads() as $t) {
                $stats['likes_received'] += $t->getLikeCount();
            }

            foreach ($user->getResponses() as $r) {
                $stats['likes_received'] += $r->getLikeCount();
            }
        }

        return $this->render('profil/profil_index.html.twig', [
            'user' => $user,
            'profil' => $profil,
            'stats' => $stats,
            'can_access_forum' => $canAccessForum
        ]);
    }

    #[Route('/profil/edit', name: 'user_profil_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if (!$session->get('user_id')) {
            return $this->redirectToRoute('app_signin');
        }

        $user = $em->getRepository(User::class)->find($session->get('user_id'));

        if (!$user) {
            $session->clear();
            return $this->redirectToRoute('app_signin');
        }

        $profil = $em->getRepository(Profil::class)
            ->findOneBy(['user' => $user]) ?? (new Profil())->setUser($user);

        $form = $this->createForm(UserEditType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ✅ Password
            if ($newPassword = $form->get('newPassword')->getData()) {
                $user->setMotDePasse(
                    $passwordHasher->hashPassword($user, $newPassword)
                );
            }

            // ✅ Image upload
            $imageFile = $request->files->get('image_file');
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/profils';
                $imageFile->move($uploadDir, $newFilename);

                if ($profil->getImage()) {
                    $oldPath = $uploadDir.'/'.$profil->getImage();
                    if (file_exists($oldPath)) unlink($oldPath);
                }

                $profil->setImage($newFilename);
            }

            // ✅ Bio
            $profil->setBio($request->request->get('bio'));

            $em->persist($profil);
            $em->flush();

            $session->set('user_name', $user->getNom());

            $this->addFlash('success', 'Profil mis à jour');
            return $this->redirectToRoute('user_profil');
        }

        return $this->render('profil/profil_edit.html.twig', [
            'user' => $user,
            'profil' => $profil,
            'userForm' => $form->createView(),
        ]);
    }

    #[Route('/profil/delete', name: 'user_profil_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        if (!$session->get('user_id')) {
            return $this->redirectToRoute('app_signin');
        }

        if (!$this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
            return $this->redirectToRoute('user_profil');
        }

        $userId = $session->get('user_id');

        $user = $em->getRepository(User::class)->find($userId);
        $profil = $em->getRepository(Profil::class)->findOneBy(['user' => $user]);
        if ($profil) {
            if ($profil->getImage()) {
                $path = $this->getParameter('kernel.project_dir').'/public/uploads/profils/'.$profil->getImage();
                if (file_exists($path)) unlink($path);
            }
            $em->remove($profil);
        }

        if ($user = $em->getRepository(User::class)->find($userId)) {
            $em->remove($user);
        }

        $em->flush();
        $session->clear();

        return $this->redirectToRoute('app_signin');
    }
}