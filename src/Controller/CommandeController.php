<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Attribute\ParamConverter;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produit = $commande->getProduit();

            if (!$produit->getActif()) {
                $this->addFlash('danger', '❌ Ce produit n\'est plus disponible.');
                return $this->render('commande/new.html.twig', ['form' => $form->createView()]);
            }

            $stock = $produit->getStock();
            if ($stock && $commande->getQuantiteCommandee() > $stock->getDisponible()) {
                $this->addFlash('danger', '⚠️ Stock insuffisant ! Disponible : ' . $stock->getDisponible() . ' unités.');
                return $this->render('commande/new.html.twig', ['form' => $form->createView()]);
            }

            $prixUnitaire = ($produit->getPromo() && $produit->getTauxPromo())
                ? $produit->getPrixPromo()
                : $produit->getPrix();
            $commande->setPrixTotal(round($prixUnitaire * $commande->getQuantiteCommandee(), 2));

            // Assigner un user par défaut (le premier user trouvé)
            if ($commande->getUser() === null) {
                $user = $userRepository->findOneBy([]);
                $commande->setUser($user);
            }

            if ($stock) {
                $stock->setDisponible($stock->getDisponible() - $commande->getQuantiteCommandee());
            }

            $em->persist($commande);
            $em->flush();

            $this->addFlash('success', '✅ Commande ajoutée ! Prix total : ' . number_format($commande->getPrixTotal(), 2) . ' DT');
            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    #[ParamConverter('commande', options: ['mapping' => ['id' => 'idCommande']])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $ancienneQuantite = $commande->getQuantiteCommandee();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouvelleQuantite = $commande->getQuantiteCommandee();
            $stock = $commande->getProduit()?->getStock();
            $difference = $nouvelleQuantite - $ancienneQuantite;

            if ($stock && $difference > 0 && $difference > $stock->getDisponible()) {
                $this->addFlash('danger', '⚠️ Stock insuffisant ! Disponible : ' . $stock->getDisponible() . ' unités.');
                return $this->render('commande/edit.html.twig', ['form' => $form->createView(), 'commande' => $commande]);
            }

            if ($stock) {
                $stock->setDisponible($stock->getDisponible() - $difference);
            }

            $produit = $commande->getProduit();
            if ($produit) {
                $prixUnitaire = ($produit->getPromo() && $produit->getTauxPromo())
                    ? $produit->getPrixPromo()
                    : $produit->getPrix();
                $commande->setPrixTotal(round($prixUnitaire * $nouvelleQuantite, 2));
            }

            // Assigner un user par défaut si null
            if ($commande->getUser() === null) {
                $user = $userRepository->findOneBy([]);
                $commande->setUser($user);
            }

            $em->flush();
            $this->addFlash('success', '✅ Commande modifiée avec succès !');
            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/edit.html.twig', [
            'form'     => $form->createView(),
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    #[ParamConverter('commande', options: ['mapping' => ['id' => 'idCommande']])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getIdCommande(), $request->request->get('_token'))) {
            $produit = $commande->getProduit();
            if ($produit && $produit->getStock()) {
                $stock = $produit->getStock();
                $stock->setDisponible($stock->getDisponible() + $commande->getQuantiteCommandee());
            }
            $em->remove($commande);
            $em->flush();
            $this->addFlash('success', '✅ Commande supprimée !');
        }

        return $this->redirectToRoute('app_commande_index');
    }
}