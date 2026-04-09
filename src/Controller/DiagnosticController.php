<?php

namespace App\Controller;

use App\Entity\Diagnostic;
use App\Form\DiagnosticType;
use App\Repository\CultureRepository;
use App\Repository\DiagnosticRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/diagnostic')]
class DiagnosticController extends AbstractController
{
    #[Route('/', name: 'app_diagnostic_index', methods: ['GET'])]
    public function index(
        Request $request,
        DiagnosticRepository $diagnosticRepository,
        CultureRepository $cultureRepository
    ): Response {
        $search        = $request->query->get('search');
        $filterCulture = $request->query->get('culture');
        $dateDebut     = $request->query->get('dateDebut');
        $dateFin       = $request->query->get('dateFin');

        $diagnostics = match (true) {
            (bool) $search                    => $diagnosticRepository->search((string) $search),
            (bool) $filterCulture             => $diagnosticRepository->filterByCulture((string) $filterCulture),
            (bool) ($dateDebut && $dateFin)   => $diagnosticRepository->filterByDateRange(new \DateTime((string) $dateDebut), new \DateTime((string) $dateFin)),
            default                           => $diagnosticRepository->findAll(),
        };

        $stats = [
            'total'      => $diagnosticRepository->countTotal(),
            'parCulture' => $diagnosticRepository->countParCulture(),
            'parMois'    => $diagnosticRepository->countParMois(),
            'recents'    => $diagnosticRepository->getRecents(5),
        ];

        return $this->render('diagnostic/diagnostic_index.html.twig', [
            'diagnostics'   => $diagnostics,
            'stats'         => $stats,
            'cultures'      => $cultureRepository->findAll(),
            'search'        => $search,
            'filterCulture' => $filterCulture,
            'dateDebut'     => $dateDebut,
            'dateFin'       => $dateFin,
        ]);
    }

    #[Route('/new', name: 'app_diagnostic_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        CultureRepository $cultureRepository
    ): Response {
        $diagnostic = new Diagnostic();
        $form = $this->createForm(DiagnosticType::class, $diagnostic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($diagnostic);
            $em->flush();
            $this->addFlash('success', 'Diagnostic ajouté avec succès !');
            return $this->redirectToRoute('app_diagnostic_index');
        }

        return $this->render('diagnostic/diagnostic_new.html.twig', [
            'form'     => $form->createView(),
            'cultures' => $cultureRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_diagnostic_show', methods: ['GET'])]
    public function show(Diagnostic $diagnostic, CultureRepository $cultureRepository): Response
    {
        $culture = $diagnostic->getIdCulture()
            ? $cultureRepository->find($diagnostic->getIdCulture())
            : null;

        return $this->render('diagnostic/diagnostic_show.html.twig', [
            'diagnostic' => $diagnostic,
            'culture'    => $culture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_diagnostic_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Diagnostic $diagnostic,
        EntityManagerInterface $em,
        CultureRepository $cultureRepository
    ): Response {
        $form = $this->createForm(DiagnosticType::class, $diagnostic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Diagnostic modifié avec succès !');
            return $this->redirectToRoute('app_diagnostic_index');
        }

        return $this->render('diagnostic/diagnostic_edit.html.twig', [
            'diagnostic' => $diagnostic,
            'form'       => $form->createView(),
            'cultures'   => $cultureRepository->findAll(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_diagnostic_delete', methods: ['POST'])]
    public function delete(Request $request, Diagnostic $diagnostic, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $diagnostic->getIdDiagnostic(), $request->request->get('_token'))) {
            $em->remove($diagnostic);
            $em->flush();
            $this->addFlash('success', 'Diagnostic supprimé avec succès !');
        }

        return $this->redirectToRoute('app_diagnostic_index');
    }
}
