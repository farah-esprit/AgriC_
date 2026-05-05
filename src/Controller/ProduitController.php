<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

    #[Route('/{id}/qrcode', name: 'app_produit_qrcode', methods: ['GET'])]
    public function qrcode(Produit $produit): Response
    {
        $writer = new SvgWriter();

        $qrCode = QrCode::create(
            "Produit : " . $produit->getNom() . "\n" .
            "Prix : " . $produit->getPrix() . " TND\n" .
            "Catégorie : " . $produit->getCategorie() . "\n" .
            "ID : " . $produit->getIdProduit()
        )
        ->setSize(300)
        ->setMargin(10);

        $result = $writer->write($qrCode);

        return $this->render('produit/qrcode.html.twig', [
            'produit'       => $produit,
            'qrCodeDataUri' => $result->getDataUri(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, HttpClientInterface $httpClient): Response
    {
        $produit = new Produit();
        $form    = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $produit->setImagePath($newFilename);
            }

            if (!$produit->getPromo()) {
                $produit->setTauxPromo(null);
            }

            $produit->setActif(true);
            $em->persist($produit);
            $em->flush();

            // Envoi WhatsApp via Twilio si le produit est en promo
            if ($produit->getPromo()) {
                $sid   = $_ENV['TWILIO_ACCOUNT_SID'];
                $token = $_ENV['TWILIO_AUTH_TOKEN'];

                $message = "🔥 Nouveau produit en promo sur AgriC !\n"
                         . "📦 Produit : " . $produit->getNom() . "\n"
                         . "💰 Prix promo : " . $produit->getPrixPromo() . " TND\n"
                         . "🏷️ Réduction : " . $produit->getTauxPromo() . "%";

                try {
                    $httpClient->request('POST',
                        'https://api.twilio.com/2010-04-01/Accounts/' . $sid . '/Messages.json',
                        [
                            'auth_basic' => [$sid, $token],
                            'body'       => [
                                'From' => $_ENV['TWILIO_WHATSAPP_FROM'],
                                'To'   => $_ENV['TWILIO_WHATSAPP_TO'],
                                'Body' => $message,
                            ],
                        ]
                    );
                    $this->addFlash('info', '📱 Notification WhatsApp envoyée !');
                } catch (\Exception $e) {
                    // WhatsApp échoue → on continue quand même
                }
            }

            flash('Produit ajouté avec succès !');
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/export', name: 'app_produit_export', methods: ['GET'])]
    public function export(ProduitRepository $produitRepository): Response
    {
        $produits   = $produitRepository->findAll();
        $csvContent = "ID,Nom,Description,Prix,Catégorie,Promo,Taux Promo,Prix Promo\n";

        foreach ($produits as $produit) {
            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $produit->getIdProduit(),
                $produit->getNom(),
                str_replace(',', ' ', $produit->getDescription() ?? ''),
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
    public function edit(Request $request, #[MapEntity(mapping: ['id' => 'idProduit'])] Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $produit->setImagePath($newFilename);
            }

            if (!$produit->getPromo()) {
                $produit->setTauxPromo(null);
            }

            $entityManager->flush();
            flash('Produit modifié avec succès !');
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['id' => 'idProduit'])] Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getIdProduit(), (string) $request->request->get('_token'))) {
            $stock = $produit->getStock();
            if ($stock) {
                $entityManager->remove($stock);
            }
            foreach ($produit->getCommandes() as $commande) {
                $entityManager->remove($commande);
            }
            $entityManager->remove($produit);
            $entityManager->flush();
            flash('Produit supprimé !');
        }

        return $this->redirectToRoute('app_produit_index');
    }
}