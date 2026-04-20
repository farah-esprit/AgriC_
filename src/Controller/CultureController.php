<?php

namespace App\Controller;

use App\Entity\Culture;
use App\Repository\CultureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/culture')]
class CultureController extends AbstractController
{
    #[Route('/', name: 'app_culture_index', methods: ['GET'])]
    public function index(Request $request, CultureRepository $cultureRepository): Response
    {
        $search         = $request->query->get('search');
        $filterType     = $request->query->get('type');
        $sortSuperficie = $request->query->get('sort');

        $cultures = match (true) {
            (bool) $search         => $cultureRepository->search($search),
            (bool) $filterType     => $cultureRepository->filterByType($filterType),
            (bool) $sortSuperficie => $cultureRepository->orderBySuperficie($sortSuperficie),
            default                => $cultureRepository->findAll(),
        };

        $stats = [
            'total'    => $cultureRepository->countTotal(),
            'moyenne'  => $cultureRepository->getSuperficieMoyenne(),
            'totaleHa' => $cultureRepository->getSuperficieTotal(),
            'max'      => $cultureRepository->getSuperficieMax(),
            'min'      => $cultureRepository->getSuperficieMin(),
            'parType'  => $cultureRepository->countParType(),
        ];

        return $this->render('culture/culture_index.html.twig', [
            'cultures'   => $cultures,
            'stats'      => $stats,
            'search'     => $search,
            'filterType' => $filterType,
        ]);
    }

    #[Route('/new', name: 'app_culture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $erreurs = [];

        if ($request->isMethod('POST')) {
            $nom          = trim($request->request->get('nom'));
            $type         = $request->request->get('type');
            $superficie   = $request->request->get('superficie');
            $localisation = trim($request->request->get('localisation'));

            $constraintString = new Regex([
                'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                'message' => 'Ce champ ne doit contenir que des lettres.',
            ]);

            foreach ($validator->validate($nom, [new NotBlank(), $constraintString]) as $v) {
                $erreurs[] = 'Nom : ' . $v->getMessage();
            }
            foreach ($validator->validate($localisation, [new NotBlank(), $constraintString]) as $v) {
                $erreurs[] = 'Localisation : ' . $v->getMessage();
            }

            if (!in_array($type, ['Céréales', 'Légumes', 'Fruits', 'Oléagineux', 'Fourragères', 'Autre'])) {
                $erreurs[] = 'Type : sélection invalide.';
            }

            if (!is_numeric($superficie) || floatval($superficie) <= 0) {
                $erreurs[] = 'Superficie : doit être un nombre positif.';
            }

            if (empty($erreurs)) {
                $culture = new Culture();
                $culture->setNom($nom);
                $culture->setType($type);
                $culture->setSuperficie(floatval($superficie));
                $culture->setLocalisation($localisation);

                $em->persist($culture);
                $em->flush();

                $this->addFlash('success', 'Culture ajoutée avec succès !');
                return $this->redirectToRoute('app_culture_index');
            }
        }

        return $this->render('culture/culture_new.html.twig', ['erreurs' => $erreurs]);
    }

    #[Route('/{id}', name: 'app_culture_show', methods: ['GET'])]
    public function show(Culture $culture): Response
    {
        return $this->render('culture/culture_show.html.twig', ['culture' => $culture]);
    }

    #[Route('/{id}/edit', name: 'app_culture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Culture $culture, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $erreurs = [];

        if ($request->isMethod('POST')) {
            $nom          = trim($request->request->get('nom'));
            $type         = $request->request->get('type');
            $superficie   = $request->request->get('superficie');
            $localisation = trim($request->request->get('localisation'));

            $constraintString = new Regex([
                'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                'message' => 'Ce champ ne doit contenir que des lettres.',
            ]);

            foreach ($validator->validate($nom, [new NotBlank(), $constraintString]) as $v) {
                $erreurs[] = 'Nom : ' . $v->getMessage();
            }
            foreach ($validator->validate($localisation, [new NotBlank(), $constraintString]) as $v) {
                $erreurs[] = 'Localisation : ' . $v->getMessage();
            }

            if (!in_array($type, ['Céréales', 'Légumes', 'Fruits', 'Oléagineux', 'Fourragères', 'Autre'])) {
                $erreurs[] = 'Type : sélection invalide.';
            }

            if (!is_numeric($superficie) || floatval($superficie) <= 0) {
                $erreurs[] = 'Superficie : doit être un nombre positif.';
            }

            if (empty($erreurs)) {
                $culture->setNom($nom);
                $culture->setType($type);
                $culture->setSuperficie(floatval($superficie));
                $culture->setLocalisation($localisation);

                $em->flush();

                $this->addFlash('success', 'Culture modifiée avec succès !');
                return $this->redirectToRoute('app_culture_index');
            }
        }

        return $this->render('culture/culture_edit.html.twig', [
            'culture' => $culture,
            'erreurs' => $erreurs,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_culture_delete', methods: ['POST'])]
    public function delete(Request $request, Culture $culture, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $culture->getIdCulture(), $request->request->get('_token'))) {
            $em->remove($culture);
            $em->flush();
            $this->addFlash('success', 'Culture supprimée avec succès !');
        }

        return $this->redirectToRoute('app_culture_index');
    }
}
