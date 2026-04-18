<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/calendrier')]
class CalendrierController extends AbstractController
{
    #[Route('/', name: 'app_calendrier_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('calendrier/index.html.twig');
    }

    #[Route('/{id}', name: 'app_calendrier_show', methods: ['GET'])]
    public function show(Activity $activity, Request $request): Response
    {
        // 🔒 Sécurité : Vérifier l'appartenance via la culture
        $userId = $request->getSession()->get('user_id');
        if (!$activity->getCulture() || !$activity->getCulture()->getUser() || $activity->getCulture()->getUser()->getUserId() !== $userId) {
            $this->addFlash('error', "Accès refusé.");
            return $this->redirectToRoute('app_calendrier_index');
        }

        return $this->render('calendrier/show.html.twig', [
            'activity' => $activity,
        ]);
    }

    #[Route('/{id}/confirm', name: 'app_calendrier_confirm', methods: ['POST'])]
    public function confirm(Activity $activity, EntityManagerInterface $em, Request $request): Response
    {
        // 🔒 Sécurité
        $userId = $request->getSession()->get('user_id');
        if (!$activity->getCulture() || !$activity->getCulture()->getUser() || $activity->getCulture()->getUser()->getUserId() !== $userId) {
            return $this->redirectToRoute('app_calendrier_index');
        }

        $activity->setIsConfirmed(true);
        // On change le titre pour enlever le "(IA)"
        $title = str_replace(' (IA)', '', $activity->getTitle());
        $activity->setTitle($title);
        
        $em->flush();

        $this->addFlash('success', 'La suggestion a été validée et ajoutée à votre planning réel.');
        return $this->redirectToRoute('app_calendrier_index');
    }

    #[Route('/{id}/delete', name: 'app_calendrier_delete', methods: ['POST'])]
    public function delete(Request $request, Activity $activity, EntityManagerInterface $em): Response
    {
        // 🔒 Sécurité
        $userId = $request->getSession()->get('user_id');
        if (!$activity->getCulture() || !$activity->getCulture()->getUser() || $activity->getCulture()->getUser()->getUserId() !== $userId) {
            return $this->redirectToRoute('app_calendrier_index');
        }

        if ($this->isCsrfTokenValid('delete'.$activity->getId(), $request->request->get('_token'))) {
            $em->remove($activity);
            $em->flush();
            $this->addFlash('info', 'Activité supprimée.');
        }

        return $this->redirectToRoute('app_calendrier_index');
    }
}
