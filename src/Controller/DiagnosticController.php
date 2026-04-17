<?php

namespace App\Controller;

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
use Symfony\Component\Routing\Attribute\ParamConverter;

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
        $search      = $request->query->get('search');
        $cultureId   = $request->query->get('culture');
        $dateDebut   = $request->query->get('dateDebut');
        $dateFin     = $request->query->get('dateFin');

        $query = $repo->findFiltered($search, $cultureId, $dateDebut, $dateFin);

        $diagnostics = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            8
        );

        $stats = $repo->getStats();

        return $this->render('diagnostic/diagnostic_index.html.twig', [
            'diagnostics'   => $diagnostics,
            'cultures'      => $cultureRepo->findAll(),
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
        $erreurs = [];

        if ($request->isMethod('POST')) {
            $symptomes = $request->request->get('symptomes');
            $cultureId = $request->request->get('idCulture');
            $infos     = $request->request->get('informationsComplementaires');
            $culture   = $cultureRepo->find($cultureId);

            /** @var UploadedFile $imageFile */
            $imageFile = $request->files->get('image');

            if (!$culture)                        $erreurs[] = 'Culture invalide';
            if (strlen(trim($symptomes ?? '')) < 5) $erreurs[] = 'Symptômes trop courts';

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
                if ($imageFile && isset($newFilename)) {
                    $imagePath = $this->imagesDirectory . '/' . $newFilename;
                    $result = $aiService->analyserImage($imagePath, $culture?->getNom(), $symptomes);
                } else {
                    $result = $aiService->analyserSymptomes($symptomes, $culture?->getNom(), $infos);
                }

                if ($result) {
                    $diagnostic->setResultat(json_encode($result));
                }

                $em->persist($diagnostic);
                $em->flush();

                $this->addFlash('success', 'Diagnostic créé avec succès !');
                return $this->redirectToRoute('app_diagnostic_index');
            }
        }

        return $this->render('diagnostic/diagnostic_new.html.twig', [
            'cultures' => $cultureRepo->findAll(),
            'erreurs'  => $erreurs,
        ]);
    }

    // =========================
    // SHOW
    // =========================
    #[Route('/{id}', name: 'app_diagnostic_show', methods: ['GET'])]
    #[ParamConverter('diagnostic', options: ['mapping' => ['id' => 'idDiagnostic']])]
    public function show(Diagnostic $diagnostic): Response
    {
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
    #[ParamConverter('diagnostic', options: ['mapping' => ['id' => 'idDiagnostic']])]
    public function edit(
        Request $request,
        Diagnostic $diagnostic,
        EntityManagerInterface $em,
        CultureRepository $cultureRepo
    ): Response {
        $erreurs = [];

        if ($request->isMethod('POST')) {
            $symptomes = $request->request->get('symptomes');
            $cultureId = $request->request->get('idCulture');
            $infos     = $request->request->get('informationsComplementaires');
            $culture   = $cultureRepo->find($cultureId);

            if (!$culture)                          $erreurs[] = 'Culture invalide';
            if (strlen(trim($symptomes ?? '')) < 5) $erreurs[] = 'Symptômes trop courts';

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
            'cultures'   => $cultureRepo->findAll(),
            'erreurs'    => $erreurs,
        ]);
    }

    // =========================
    // DELETE
    // =========================
    #[Route('/{id}/delete', name: 'app_diagnostic_delete', methods: ['POST'])]
    #[ParamConverter('diagnostic', options: ['mapping' => ['id' => 'idDiagnostic']])]
    public function delete(
        Request $request,
        Diagnostic $diagnostic,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $diagnostic->getIdDiagnostic(), $request->request->get('_token'))) {
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
    #[ParamConverter('diagnostic', options: ['mapping' => ['id' => 'idDiagnostic']])]
    public function pdf(Diagnostic $diagnostic): Response
    {
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