<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    private const STRIPE_PUB = '';
    private const STRIPE_SEC = '';

    #[Route('/checkout/{id}', name: 'app_payment_checkout', methods: ['GET'])]
    public function checkout(Commande $commande): Response
    {
        return $this->render('payment/checkout.html.twig', [
            'commande'       => $commande,
            'stripe_pub_key' => self::STRIPE_PUB,
        ]);
    }

    #[Route('/process/{id}', name: 'app_payment_process', methods: ['POST'])]
    public function process(Request $request, Commande $commande, EntityManagerInterface $em): JsonResponse
    {
        \Stripe\Stripe::setApiKey(self::STRIPE_SEC);

        try {
            $prixTotal = $commande->getPrixTotal();

            if (!$prixTotal || $prixTotal <= 0) {
                $produit = $commande->getProduit();
                if ($produit) {
                    $prixTotal = $produit->getPrix() * $commande->getQuantiteCommandee();
                    $commande->setPrixTotal($prixTotal);
                    $em->flush();
                }
            }

            if (!$prixTotal || $prixTotal <= 0) {
                return $this->json(['error' => 'Le montant doit être supérieur à 0.'], 400);
            }

            $montant = (int) ($prixTotal * 100);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount'   => $montant,
                'currency' => 'eur',
                'metadata' => [
                    'commande_id' => $commande->getIdCommande(),
                    'produit'     => $commande->getProduit()?->getNom() ?? 'N/A',
                ],
            ]);

            return $this->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/success/{id}', name: 'app_payment_success', methods: ['GET'])]
    public function success(Commande $commande, EntityManagerInterface $em): Response
    {
        $commande->setStatut('PAYEE');
        $em->flush();

        return $this->render('payment/success.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/cancel/{id}', name: 'app_payment_cancel', methods: ['GET'])]
    public function cancel(Commande $commande): Response
    {
        return $this->render('payment/cancel.html.twig', [
            'commande' => $commande,
        ]);
    }
}
