<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $repo): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'dateCreation');
        $direction = $request->query->get('direction', 'DESC');

        $reclamations = $repo->findBySearchAndSort($search, $sort, $direction);

        // Récupération des statistiques
        $statsStatus = $repo->countByStatus();
        $statsPriority = $repo->countByPriority();

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'statsStatus' => $statsStatus,
            'statsPriority' => $statsPriority,
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($userId = $session->get('user_id')) {
                $user = $em->getRepository(User::class)->find($userId);
                $reclamation->setUtilisateur($user);
            }

            $em->persist($reclamation);
            $em->flush();

            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{idReclamation}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['idReclamation' => 'idReclamation'])] Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{idReclamation}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['idReclamation' => 'idReclamation'])] Reclamation $reclamation, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{idReclamation}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['idReclamation' => 'idReclamation'])] Reclamation $reclamation, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete'.$reclamation->getIdReclamation(), is_string($token) ? $token : null)) {
            $em->remove($reclamation);
            $em->flush();
        }

        return $this->redirectToRoute('app_reclamation_index');
    }
}