<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    public function new(Request $request, EntityManagerInterface $em, ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        if ($request->isMethod('POST')) {
            $produit = $produitRepository->find($request->request->get('produit_id'));
            if (!$produit) {
                $this->addFlash('danger', '❌ Produit introuvable.');
                return $this->render('commande/new.html.twig', ['produits' => $produits]);
            }

            if (!$produit->getActif()) {
                $this->addFlash('danger', '❌ Ce produit n\'est plus disponible.');
                return $this->render('commande/new.html.twig', ['produits' => $produits]);
            }

            $quantiteCommandee = (int) $request->request->get('quantiteCommandee');
            if ($quantiteCommandee < 1) {
                $this->addFlash('danger', '❌ La quantité doit être au moins 1.');
                return $this->render('commande/new.html.twig', ['produits' => $produits]);
            }

            $dateCommande = $request->request->get('dateCommande');
            if ($dateCommande < date('Y-m-d')) {
                $this->addFlash('danger', '❌ La date ne peut pas être dans le passé.');
                return $this->render('commande/new.html.twig', ['produits' => $produits]);
            }

            $stock = $produit->getStock();
            if ($stock && $quantiteCommandee > $stock->getDisponible()) {
                $this->addFlash('danger', '⚠️ Stock insuffisant ! Disponible : ' . $stock->getDisponible() . ' unités.');
                return $this->render('commande/new.html.twig', ['produits' => $produits]);
            }

            $prixUnitaire = ($produit->getPromo() && $produit->getTauxPromo())
                ? $produit->getPrixPromo()
                : $produit->getPrix();
            $prixTotal = round($prixUnitaire * $quantiteCommandee, 2);

            $commande = new Commande();
            $commande->setDateCommande($dateCommande);
            $commande->setStatut($request->request->get('statut') ?? 'EN_ATTENTE');
            $commande->setQuantiteCommandee($quantiteCommandee);
            $commande->setProduit($produit);
            $commande->setPrixTotal($prixTotal);

            if ($stock) {
                $stock->setDisponible($stock->getDisponible() - $quantiteCommandee);
            }

            $em->persist($commande);
            $em->flush();

            $this->addFlash('success', '✅ Commande ajoutée ! Prix total : ' . number_format($prixTotal, 2) . ' DT');
            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/new.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $ancienneQuantite = $commande->getQuantiteCommandee();
            $nouvelleQuantite = (int) $request->request->get('quantiteCommandee');
            $dateCommande     = $request->request->get('dateCommande');

            if ($dateCommande < date('Y-m-d')) {
                $this->addFlash('danger', '❌ La date ne peut pas être dans le passé.');
                return $this->render('commande/edit.html.twig', ['commande' => $commande]);
            }

            if ($nouvelleQuantite < 1) {
                $this->addFlash('danger', '❌ La quantité doit être au moins 1.');
                return $this->render('commande/edit.html.twig', ['commande' => $commande]);
            }

            $stock      = $commande->getProduit()?->getStock();
            $difference = $nouvelleQuantite - $ancienneQuantite;

            if ($stock && $difference > 0 && $difference > $stock->getDisponible()) {
                $this->addFlash('danger', '⚠️ Stock insuffisant ! Disponible : ' . $stock->getDisponible() . ' unités.');
                return $this->render('commande/edit.html.twig', ['commande' => $commande]);
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

            $commande->setStatut($request->request->get('statut'));
            $commande->setQuantiteCommandee($nouvelleQuantite);
            $commande->setDateCommande($dateCommande);

            $em->flush();
            $this->addFlash('success', '✅ Commande modifiée avec succès !');
            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
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
