<?php

namespace App\Controller;

use App\Entity\Culture;
use App\Entity\Diagnostic;
use App\Repository\CultureRepository;
use App\Repository\DiagnosticRepository;
use App\Service\DiagnosticAiService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/diagnostic')]
class DiagnosticController extends AbstractController
{
    private string $imagesDirectory;

    public function __construct(string $images_directory)
    {
        $this->imagesDirectory = $images_directory;
    }

    // =========================
    // LISTE
    // =========================
    #[Route('/', name: 'app_diagnostic_index', methods: ['GET'])]
    public function index(
        Request $request,
        DiagnosticRepository $repo,
        CultureRepository $cultureRepo,
        PaginatorInterface $paginator
    ): Response {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('app_signin');
        }

        $search      = $request->query->get('search');
        $cultureId   = $request->query->get('culture');
        $dateDebut   = $request->query->get('dateDebut');
        $dateFin     = $request->query->get('dateFin');

        $query = $repo->findFilteredByUser($userId, $search, $cultureId, $dateDebut, $dateFin);

        $diagnostics = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            8
        );

        $stats = $repo->getStatsByUser($userId);

        return $this->render('diagnostic/diagnostic_index.html.twig', [
            'diagnostics'   => $diagnostics,
            'cultures'      => $cultureRepo->findBy(['user' => $userId]),
            'stats'         => $stats,
            'search'        => $search,
            'filterCulture' => $cultureId,
            'dateDebut'     => $dateDebut,
            'dateFin'       => $dateFin,
        ]);
    }

    // =========================
    // CREATE
    // =========================
    #[Route('/new', name: 'app_diagnostic_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        CultureRepository $cultureRepo,
        DiagnosticAiService $aiService
    ): Response {
        $userId = $request->getSession()->get('user_id');
        $erreurs = [];

        if ($request->isMethod('POST')) {
            $symptomesRaw = $request->request->get('symptomes');
            $symptomes = is_string($symptomesRaw) ? $symptomesRaw : null;
            
            $idCultureRaw = $request->request->get('idCulture');
            $culture = $idCultureRaw !== null ? $em->getReference(Culture::class, $idCultureRaw) : null;
            
            $infosRaw = $request->request->get('informationsComplementaires');
            $infos = is_string($infosRaw) ? $infosRaw : null;

            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image');

            if (!$culture || ($culture->getUser() && $culture->getUser()->getUserId() !== $userId)) {
                $erreurs[] = 'Culture invalide';
            }
            if (strlen(trim($symptomes ?? '')) < 5) {
                $erreurs[] = 'Symptômes trop courts';
            }

            if (empty($erreurs)) {
                $diagnostic = new Diagnostic();
                $diagnostic->setSymptomes($symptomes);
                $diagnostic->setCulture($culture);
                $diagnostic->setInformationsComplementaires($infos);
                $diagnostic->setDateDiagnostic((new \DateTime())->format('Y-m-d'));

                // Gestion de l'upload d'image
                if ($imageFile) {
                    $newFilename = uniqid('diag-', true) . '.' . $imageFile->guessExtension();
                    try {
                        $imageFile->move($this->imagesDirectory, $newFilename);
                        $diagnostic->setImage($newFilename);
                    } catch (\Exception $e) {
                        $erreurs[] = "Erreur lors du transfert de l'image : " . $e->getMessage();
                    }
                }

                // Analyse IA
                if ($imageFile) {
                    $imagePath = $this->imagesDirectory . '/' . $newFilename;
                    $result = $aiService->analyserImage($imagePath, $culture?->getNom(), $symptomes ?? '');
                } else {
                    $result = $aiService->analyserSymptomes($symptomes ?? '', $culture?->getNom(), $infos);
                }

                if ($result) {
                    $encodedResult = json_encode($result);
                    $diagnostic->setResultat($encodedResult !== false ? $encodedResult : null);
                }

                $em->persist($diagnostic);
                $em->flush();

                $this->addFlash('success', 'Diagnostic créé avec succès !');
                return $this->redirectToRoute('app_diagnostic_index');
            }
        }

        return $this->render('diagnostic/diagnostic_new.html.twig', [
            'cultures' => $cultureRepo->findBy(['user' => $userId]),
            'erreurs'  => $erreurs,
        ]);
    }

    // =========================
    // SHOW
    // =========================
    #[Route('/{id}', name: 'app_diagnostic_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['id' => 'idDiagnostic'])] Diagnostic $diagnostic, Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$diagnostic->getCulture() || !$diagnostic->getCulture()->getUser() || $diagnostic->getCulture()->getUser()->getUserId() !== $userId) {
            $this->addFlash('error', "Accès refusé : Ce diagnostic ne vous appartient pas.");
            return $this->redirectToRoute('app_diagnostic_index');
        }

        $result = $diagnostic->getResultat()
            ? json_decode($diagnostic->getResultat(), true)
            : null;

        return $this->render('diagnostic/diagnostic_show.html.twig', [
            'diagnostic' => $diagnostic,
            'ai_result'  => $result,
        ]);
    }

    // =========================
    // EDIT
    // =========================
    #[Route('/{id}/edit', name: 'app_diagnostic_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id' => 'idDiagnostic'])] Diagnostic $diagnostic,
        EntityManagerInterface $em,
        CultureRepository $cultureRepo
    ): Response {
        $userId = $request->getSession()->get('user_id');
        if (!$diagnostic->getCulture() || !$diagnostic->getCulture()->getUser() || $diagnostic->getCulture()->getUser()->getUserId() !== $userId) {
            $this->addFlash('error', "Accès refusé.");
            return $this->redirectToRoute('app_diagnostic_index');
        }

        $erreurs = [];

        if ($request->isMethod('POST')) {
            $symptomesRaw = $request->request->get('symptomes');
            $symptomes = is_string($symptomesRaw) ? $symptomesRaw : null;

            $idCultureRaw = $request->request->get('idCulture');
            $culture = $idCultureRaw !== null ? $em->getReference(Culture::class, $idCultureRaw) : null;

            $infosRaw = $request->request->get('informationsComplementaires');
            $infos = is_string($infosRaw) ? $infosRaw : null;

            if (!$culture || ($culture->getUser() && $culture->getUser()->getUserId() !== $userId)) {
                $erreurs[] = 'Culture invalide';
            }
            if (strlen(trim($symptomes ?? '')) < 5) {
                $erreurs[] = 'Symptômes trop courts';
            }

            if (empty($erreurs)) {
                $diagnostic->setSymptomes($symptomes);
                $diagnostic->setCulture($culture);
                $diagnostic->setInformationsComplementaires($infos);

                $em->flush();

                $this->addFlash('success', 'Diagnostic modifié avec succès.');
                return $this->redirectToRoute('app_diagnostic_index');
            }
        }

        return $this->render('diagnostic/diagnostic_edit.html.twig', [
            'diagnostic' => $diagnostic,
            'cultures'   => $cultureRepo->findBy(['user' => $userId]),
            'erreurs'    => $erreurs,
        ]);
    }

    // =========================
    // DELETE
    // =========================
    #[Route('/{id}/delete', name: 'app_diagnostic_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        #[MapEntity(mapping: ['id' => 'idDiagnostic'])] Diagnostic $diagnostic,
        EntityManagerInterface $em
    ): Response {
        $userId = $request->getSession()->get('user_id');
        if (!$diagnostic->getCulture() || !$diagnostic->getCulture()->getUser() || $diagnostic->getCulture()->getUser()->getUserId() !== $userId) {
            $this->addFlash('error', "Accès refusé : Vous ne pouvez pas supprimer ce diagnostic.");
            return $this->redirectToRoute('app_diagnostic_index');
        }

        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $diagnostic->getIdDiagnostic(), is_string($token) ? $token : null)) {
            $em->remove($diagnostic);
            $em->flush();
            $this->addFlash('success', 'Diagnostic supprimé.');
        }

        return $this->redirectToRoute('app_diagnostic_index');
    }

    // =========================
    // PDF INDIVIDUEL
    // =========================
    #[Route('/{id}/pdf', name: 'app_diagnostic_pdf', methods: ['GET'])]
    public function pdf(#[MapEntity(mapping: ['id' => 'idDiagnostic'])] Diagnostic $diagnostic, Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$diagnostic->getCulture() || !$diagnostic->getCulture()->getUser() || $diagnostic->getCulture()->getUser()->getUserId() !== $userId) {
            return $this->redirectToRoute('app_diagnostic_index');
        }

        $html = $this->renderView('diagnostic/pdf/diagnostic_pdf.html.twig', [
            'diagnostic' => $diagnostic,
            'date'       => new \DateTime(),
            'ai_result'  => $diagnostic->getResultat()
                ? json_decode($diagnostic->getResultat(), true)
                : null,
        ]);

        return $this->buildPdf($html, 'diagnostic-' . $diagnostic->getIdDiagnostic() . '.pdf');
    }

    // =========================
    // PDF LISTE COMPLÈTE
    // =========================
    #[Route('/pdf/liste', name: 'app_diagnostic_pdf_liste', methods: ['GET'])]
    public function pdfListe(DiagnosticRepository $repo): Response
    {
        $html = $this->renderView('diagnostic/pdf/pdf_liste.html.twig', [
            'diagnostics' => $repo->findAll(),
            'date'        => new \DateTime(),
        ]);

        return $this->buildPdf($html, 'diagnostics-liste.pdf', 'landscape');
    }

    // =========================
    // AI ENDPOINT (AJAX)
    // =========================
    #[Route('/api/ai', name: 'app_diagnostic_ai', methods: ['POST'])]
    public function ai(
        Request $request,
        DiagnosticAiService $aiService,
        CultureRepository $cultureRepo
    ): JsonResponse {
        try {
            $isMultipart = str_contains($request->headers->get('Content-Type') ?? '', 'multipart/form-data');
            
            if ($isMultipart) {
                $symptomes = $request->request->get('symptomes');
                $idCulture = $request->request->get('idCulture');
                $imageFile = $request->files->get('image');
                $infos     = $request->request->get('infos');
            } else {
                $data      = json_decode($request->getContent(), true);
                $symptomes = $data['symptomes'] ?? '';
                $idCulture = $data['idCulture'] ?? null;
                $imageFile = null;
                $infos     = $data['infos'] ?? null;
            }

            if (empty($symptomes) && !$imageFile) {
                return $this->json(['error' => 'Les symptômes ou une image sont obligatoires'], 400);
            }

            $culture = $cultureRepo->find($idCulture);

            if ($imageFile instanceof UploadedFile) {
                $result = $aiService->analyserImage($imageFile->getPathname(), $culture?->getNom(), $symptomes);
            } else {
                $result = $aiService->analyserSymptomes($symptomes, $culture?->getNom(), $infos);
            }

            if (!$result) {
                return $this->json(['error' => 'IA indisponible'], 500);
            }

            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de l\'analyse: ' . $e->getMessage()], 500);
        }
    }

    // =========================
    // HELPER PDF
    // =========================
    private function buildPdf(string $html, string $filename, string $orientation = 'portrait'): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }
}