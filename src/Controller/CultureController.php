<?php

namespace App\Controller;

use App\Entity\Culture;
use App\Repository\CultureRepository;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index(
        Request $request,
        CultureRepository $cultureRepository,
        PaginatorInterface $paginator,
        WeatherService $weatherService
    ): Response {
        $search         = $request->query->get('search');
        $filterType     = $request->query->get('type');
        $sortSuperficie = $request->query->get('sort');

        $query = match (true) {
            (bool) $search         => $cultureRepository->search($search),
            (bool) $filterType     => $cultureRepository->filterByType($filterType),
            (bool) $sortSuperficie => $cultureRepository->orderBySuperficie($sortSuperficie),
            default                => $cultureRepository->findAll(),
        };

        // 📄 Pagination via KnpPaginatorBundle
        $cultures = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        $stats = [
            'total'    => $cultureRepository->countTotal(),
            'moyenne'  => $cultureRepository->getSuperficieMoyenne(),
            'totaleHa' => $cultureRepository->getSuperficieTotal(),
            'max'      => $cultureRepository->getSuperficieMax(),
            'min'      => $cultureRepository->getSuperficieMin(),
            'parType'  => $cultureRepository->countParType(),
        ];

        // 🌤️ Météo pour le widget fixe en haut de la page
        $villeMeteo = $request->query->get('ville', 'Tunis');
        $meteo      = $weatherService->getWeather($villeMeteo);

        return $this->render('culture/culture_index.html.twig', [
            'cultures'   => $cultures,
            'stats'      => $stats,
            'search'     => $search,
            'filterType' => $filterType,
            'meteo'      => $meteo,
            'villeMeteo' => $villeMeteo,
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

    // ⚠️ Routes spécifiques DOIVENT être avant les routes paramétrées
    #[Route('/analyser-image', name: 'app_culture_analyze_image', methods: ['GET'])]
    public function analyzeImage(): Response
    {
        return $this->render('culture/analyze_image.html.twig');
    }

    /**
     * Affiche le détail d'une culture + météo en temps réel via OpenWeatherMap API.
     */
    #[Route('/{id}', name: 'app_culture_show', methods: ['GET'])]
    public function show(Culture $culture, WeatherService $weatherService): Response
    {
        // 🌤️ Météo basée sur la localisation de la culture
        $meteo = $weatherService->getWeather($culture->getLocalisation() ?? 'Tunis');

        return $this->render('culture/culture_show.html.twig', [
            'culture' => $culture,
            'meteo'   => $meteo,
        ]);
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

