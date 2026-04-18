<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\User;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evenement')]
class EvenementController extends AbstractController
{
    #[Route('/', name: 'app_evenement_index', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $evenementRepository): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'dateDebut');
        $direction = $request->query->get('direction', 'ASC');

        $evenements = $evenementRepository->findBySearchAndSort($search, $sort, $direction);

        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
        ]);
    }

    #[Route('/{idEvenement}/rate', name: 'app_evenement_rate', methods: ['POST'])]
    public function rate(
        Request $request, 
        Evenement $evenement, 
        EntityManagerInterface $entityManager, 
        SessionInterface $session
    ): Response {
        $userId = $session->get('user_id');
        if (!$userId) {
            $this->addFlash('error', 'Vous devez être connecté pour noter un événement.');
            return $this->redirectToRoute('app_evenement_index');
        }

        $noteValue = $request->request->get('note');
        if (!$noteValue || $noteValue < 1 || $noteValue > 5) {
            $this->addFlash('error', 'Note invalide.');
            return $this->redirectToRoute('app_evenement_index');
        }

        $user = $entityManager->getRepository(User::class)->find($userId);
        $noteRepo = $entityManager->getRepository(\App\Entity\NoteEvenement::class);
        
        $existingNote = $noteRepo->findOneBy([
            'evenement' => $evenement,
            'utilisateur' => $user
        ]);

        if ($existingNote) {
            $existingNote->setValeur($noteValue);
        } else {
            $note = new \App\Entity\NoteEvenement();
            $note->setEvenement($evenement);
            $note->setUtilisateur($user);
            $note->setValeur($noteValue);
            $entityManager->persist($note);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Merci pour votre note !');

        return $this->redirectToRoute('app_evenement_index');
    }

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userId = $session->get('user_id');
            if ($userId) {
                $user = $entityManager->getRepository(User::class)->find($userId);
                $evenement->setOrganisateur($user);
            }

            $entityManager->persist($evenement);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été ajouté avec succès.');
            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{idEvenement}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement, WeatherService $weatherService): Response
    {
        $lieu = $evenement->getLieu();
        $weather = null;
        $coordinates = null;

        if ($lieu) {
            // Extraire la première partie du lieu (ville)
            $city = explode(',', $lieu)[0];
            $weather = $weatherService->getWeatherForCity($city);
            $coordinates = $weatherService->getCoordinatesForCity($city);
        }

        return $this->render('evenement/show.html.twig', [
            'evenement'   => $evenement,
            'weather'     => $weather,
            'coordinates' => $coordinates,
        ]);
    }

    #[Route('/{idEvenement}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été modifié avec succès.');
            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/{idEvenement}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getIdEvenement(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
            $this->addFlash('success', 'L\'événement a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }
}
