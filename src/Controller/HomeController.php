<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(SessionInterface $session): Response
    {
        // Déterminer le bon layout selon le rôle
        if ($session->get('user_type') === 'ADMIN') {
            $layout = 'admin/base_admin.html.twig';
        } elseif ($session->get('user_role') === 'FOURNISSEUR') {
            $layout = 'fournisseur.html.twig';
        } elseif ($session->get('user_role') === 'AGRICULTEUR') {
            $layout = 'agriculteur.html.twig';
        } else {
            $layout = 'base.html.twig'; // visiteur non connecté
        }

        return $this->render('home/index.html.twig', [
            'layout' => $layout,
        ]);
    }
}