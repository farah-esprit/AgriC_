<?php

namespace App\Service;

use App\Entity\Diagnostic;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

/**
 * Service d'export PDF des diagnostics agricoles.
 * Utilise le bundle dompdf pour générer des fichiers PDF téléchargeables.
 */
class DiagnosticPdfService
{
    public function __construct(
        private readonly Environment $twig
    ) {}

    /**
     * Génère un PDF pour un diagnostic unique.
     */
    public function generateDiagnosticPdf(Diagnostic $diagnostic): string
    {
        $html = $this->twig->render('diagnostic/pdf/diagnostic_pdf.html.twig', [
            'diagnostic' => $diagnostic,
            'date'       => new \DateTime(),
        ]);

        return $this->renderPdf($html);
    }

    /**
     * Génère un PDF pour la liste complète des diagnostics.
     *
     * @param Diagnostic[] $diagnostics
     */
    public function generateListePdf(array $diagnostics): string
    {
        $html = $this->twig->render('diagnostic/pdf/diagnostics_liste_pdf.html.twig', [
            'diagnostics' => $diagnostics,
            'date'        => new \DateTime(),
        ]);

        return $this->renderPdf($html);
    }

    private function renderPdf(string $html): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
