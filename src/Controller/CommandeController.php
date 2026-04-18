<?php

namespace App\Controller;

use App\Service\PdfService;
use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CommandeMailer;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/{id}/pdf', name: 'app_commande_pdf', methods: ['GET'])]
    public function pdf(Commande $commande, PdfService $pdfService): Response
    {
        $html = $this->renderView('commande/pdf.html.twig', [
            'commande' => $commande,
        ]);

        $pdfService->showPdfFile($html);
        
        return new Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        CommandeMailer $commandeMailer
    ): Response {

        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $produit = $commande->getProduit();

            if (!$produit->getActif()) {
                $this->addFlash('danger', '❌ Produit indisponible');
                return $this->render('commande/new.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            $stock = $produit->getStock();

            if ($stock && $commande->getQuantiteCommandee() > $stock->getDisponible()) {
                $this->addFlash('danger', '⚠️ Stock insuffisant');
                return $this->render('commande/new.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            // calcul prix
            $prixUnitaire = ($produit->getPromo() && $produit->getTauxPromo())
                ? $produit->getPrixPromo()
                : $produit->getPrix();

            $commande->setPrixTotal(
                round($prixUnitaire * $commande->getQuantiteCommandee(), 2)
            );

            // user par défaut
            if ($commande->getUser() === null) {
                $user = $userRepository->findOneBy([]);
                $commande->setUser($user);
            }

            // update stock
            if ($stock) {
                $stock->setDisponible(
                    $stock->getDisponible() - $commande->getQuantiteCommandee()
                );
            }

            $em->persist($commande);
            $em->flush();

            try {
                $commandeMailer->sendConfirmation($commande);
                $commandeMailer->sendNewOrderNotificationToAdmins($commande);
                $this->addFlash('success', '✅ Commande ajoutée + emails de confirmation envoyés !');
            } catch (\Throwable $e) {
                $this->addFlash('warning', '⚠️ Commande ajoutée, mais les emails n\'ont pas pu être envoyés.');
            }

            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Commande $commande,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        CommandeMailer $commandeMailer
    ): Response {

        $ancienneQuantite = $commande->getQuantiteCommandee();

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $nouvelleQuantite = $commande->getQuantiteCommandee();
            $stock = $commande->getProduit()?->getStock();
            $difference = $nouvelleQuantite - $ancienneQuantite;

            if ($stock && $difference > 0 && $difference > $stock->getDisponible()) {
                $this->addFlash('danger', '⚠️ Stock insuffisant');
                return $this->render('commande/edit.html.twig', [
                    'form' => $form->createView(),
                    'commande' => $commande
                ]);
            }

            if ($stock) {
                $stock->setDisponible($stock->getDisponible() - $difference);
            }

            // recalcul prix
            $produit = $commande->getProduit();

            if ($produit) {
                $prixUnitaire = ($produit->getPromo() && $produit->getTauxPromo())
                    ? $produit->getPrixPromo()
                    : $produit->getPrix();

                $commande->setPrixTotal(
                    round($prixUnitaire * $nouvelleQuantite, 2)
                );
            }

            // user fallback
            if ($commande->getUser() === null) {
                $user = $userRepository->findOneBy([]);
                $commande->setUser($user);
            }

            $em->flush();

            try {
                $commandeMailer->sendStatutUpdate($commande);
            } catch (\Throwable $e) {}

            $this->addFlash('success', '✅ Commande modifiée');
            return $this->redirectToRoute('app_commande_index');
        }

        return $this->render('commande/edit.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Commande $commande,
        EntityManagerInterface $em
    ): Response {

        if ($this->isCsrfTokenValid('delete' . $commande->getIdCommande(), $request->request->get('_token'))) {

            $produit = $commande->getProduit();

            if ($produit && $produit->getStock()) {
                $stock = $produit->getStock();
                $stock->setDisponible(
                    $stock->getDisponible() + $commande->getQuantiteCommandee()
                );
            }

            $em->remove($commande);
            $em->flush();

            $this->addFlash('success', '✅ Commande supprimée');
        }

        return $this->redirectToRoute('app_commande_index');
    }
}