<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Form\StockType;
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
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $stock = new Stock();
       $form = $this->createForm(StockType::class, $stock, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produit = $stock->getProduit();

            if ($produit->getStock() !== null) {
                $this->addFlash('danger', 'Ce produit a déjà un stock associé.');
                return $this->render('stock/new.html.twig', ['form' => $form->createView()]);
            }

            if ($stock->getDisponible() > $stock->getQuantite()) {
                $this->addFlash('danger', 'La quantité disponible ne peut pas dépasser la quantité totale.');
                return $this->render('stock/new.html.twig', ['form' => $form->createView()]);
            }

            $em->persist($stock);
            $em->flush();

            $this->addFlash('success', 'Stock ajouté avec succès.');
            return $this->redirectToRoute('app_stock_index');
        }

        return $this->render('stock/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stock $stock, EntityManagerInterface $em): Response
    {
       $form = $this->createForm(StockType::class, $stock, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($stock->getDisponible() > $stock->getQuantite()) {
                $this->addFlash('danger', 'La quantité disponible ne peut pas dépasser la quantité totale.');
                return $this->render('stock/edit.html.twig', ['form' => $form->createView(), 'stock' => $stock]);
            }

            $em->flush();
            $this->addFlash('success', 'Stock modifié.');
            return $this->redirectToRoute('app_stock_index');
        }

        return $this->render('stock/edit.html.twig', [
            'form'  => $form->createView(),
            'stock' => $stock,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_stock_delete', methods: ['POST'])]
    public function delete(Request $request, Stock $stock, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $stock->getIdStock(), $request->request->get('_token'))) {
            $em->remove($stock);
            $em->flush();
            $this->addFlash('success', 'Stock supprimé.');
        }

        return $this->redirectToRoute('app_stock_index');
    }
}
