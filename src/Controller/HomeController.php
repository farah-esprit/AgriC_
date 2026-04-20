<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Restauration de la route d'accueil principale suite à votre demande.
        // Si vous aviez un template spécifique pour l'accueil, vous pouvez le render ici.
        return $this->render('base.html.twig');
    }
}