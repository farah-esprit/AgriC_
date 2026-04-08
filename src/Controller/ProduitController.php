<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $produit = new Produit();
            $produit->setNom($request->request->get('nom'));
            $produit->setDescription($request->request->get('description'));
            $produit->setPrix((float) $request->request->get('prix'));
            $produit->setCategorie($request->request->get('categorie'));
            $produit->setActif(true);

            $isPromo = (bool) $request->request->get('promo');
            $produit->setPromo($isPromo);
            $produit->setTauxPromo($isPromo ? (float) $request->request->get('tauxPromo') : null);

            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $produit->setImagePath($newFilename);
            }

            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('produit/new.html.twig');
    }

    #[Route('/export', name: 'app_produit_export', methods: ['GET'])]
    public function export(ProduitRepository $produitRepository): Response
    {
        $produits    = $produitRepository->findAll();
        $csvContent  = "ID,Nom,Description,Prix,Catégorie,Promo,Taux Promo,Prix Promo\n";

        foreach ($produits as $produit) {
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $produit->getIdProduit(),
                $produit->getNom(),
                str_replace(',', ' ', $produit->getDescription()),
                $produit->getPrix(),
                $produit->getCategorie(),
                $produit->getPromo() ? 'Oui' : 'Non',
                $produit->getTauxPromo() ? $produit->getTauxPromo() . '%' : '-',
                $produit->getPromo() ? $produit->getPrixPromo() : '-'
            );
        }

        return new Response($csvContent, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="produits.csv"',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $produit->setNom($request->request->get('nom'));
            $produit->setDescription($request->request->get('description'));
            $produit->setPrix((float) $request->request->get('prix'));
            $produit->setCategorie($request->request->get('categorie'));

            $isPromo = (bool) $request->request->get('promo');
            $produit->setPromo($isPromo);
            $produit->setTauxPromo($isPromo ? (float) $request->request->get('tauxPromo') : null);

            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $produit->setImagePath($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('produit/edit.html.twig', ['produit' => $produit]);
    }

    #[Route('/{id}/delete', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getIdProduit(), $request->request->get('_token'))) {
            $stock = $produit->getStock();
            if ($stock) {
                $em->remove($stock);
            }

            foreach ($produit->getCommandes() as $commande) {
                $em->remove($commande);
            }

            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé !');
        }

        return $this->redirectToRoute('app_produit_index');
    }
}
