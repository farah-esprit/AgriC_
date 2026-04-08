<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Repository\StockRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock')]
class StockController extends AbstractController
{
    #[Route('/', name: 'app_stock_index', methods: ['GET'])]
    public function index(StockRepository $stockRepository): Response
    {
        return $this->render('stock/index.html.twig', [
            'stocks' => $stockRepository->findAllWithProduit(),
        ]);
    }

    #[Route('/new', name: 'app_stock_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ProduitRepository $produitRepository
    ): Response {
        // ✅ Produits sans stock uniquement
        $produits = array_filter(
            $produitRepository->findAll(),
            fn($p) => $p->getStock() === null
        );

        if ($request->isMethod('POST')) {

            $produit = $produitRepository->find($request->request->get('produit_id'));

            if (!$produit) {
                $this->addFlash('danger', 'Produit introuvable.');
                return $this->render('stock/new.html.twig', compact('produits'));
            }

            if ($produit->getStock()) {
                $this->addFlash('danger', 'Ce produit a déjà un stock.');
                return $this->render('stock/new.html.twig', compact('produits'));
            }

            $quantite = (int) $request->request->get('quantite');
            $disponible = (int) $request->request->get('disponible');
            $seuilAlert = (int) $request->request->get('seuilAlert');

            // ✅ Validations
            if ($disponible > $quantite) {
                $this->addFlash('danger', 'Disponible > Quantité.');
                return $this->render('stock/new.html.twig', compact('produits'));
            }

            if ($seuilAlert > $quantite) {
                $this->addFlash('danger', 'Seuil > Quantité.');
                return $this->render('stock/new.html.twig', compact('produits'));
            }

            $stock = (new Stock())
                ->setQuantite($quantite)
                ->setDisponible($disponible)
                ->setSeuilAlert($seuilAlert)
                ->setProduit($produit);

            $em->persist($stock);
            $em->flush();

            $this->addFlash('success', 'Stock ajouté avec succès.');
            return $this->redirectToRoute('app_stock_index');
        }

        return $this->render('stock/new.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stock $stock, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {

            $quantite = (int) $request->request->get('quantite');
            $disponible = (int) $request->request->get('disponible');
            $seuilAlert = (int) $request->request->get('seuilAlert');

            if ($disponible > $quantite) {
                $this->addFlash('danger', 'Disponible > Quantité.');
                return $this->render('stock/edit.html.twig', compact('stock'));
            }

            if ($seuilAlert > $quantite) {
                $this->addFlash('danger', 'Seuil > Quantité.');
                return $this->render('stock/edit.html.twig', compact('stock'));
            }

            $stock
                ->setQuantite($quantite)
                ->setDisponible($disponible)
                ->setSeuilAlert($seuilAlert);

            $em->flush();

            $this->addFlash('success', 'Stock modifié.');
            return $this->redirectToRoute('app_stock_index');
        }

        return $this->render('stock/edit.html.twig', [
            'stock' => $stock,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_stock_delete', methods: ['POST'])]
    public function delete(Request $request, Stock $stock, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stock->getIdStock(), $request->request->get('_token'))) {
            $em->remove($stock);
            $em->flush();

            $this->addFlash('success', 'Stock supprimé.');
        }

        return $this->redirectToRoute('app_stock_index');
    }
}