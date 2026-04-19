# 📄 GUIDE COMPLET - KnpPaginator Bundle

## ✅ Statut Actuel

KnpPaginatorBundle est **déjà installé et configuré** dans votre projet!

- ✅ **composer.json** : `"knplabs/knp-paginator-bundle": "^6.10"`
- ✅ **DiagnosticController** : Utilise la pagination (ligne 40-44)
- ✅ **CultureController** : Utilise la pagination (ligne 40-44)
- ✅ **Templates** : Affichent déjà les paginations

---

## 🎯 Configuration Optimale

### Fichier de Configuration
**Créé**: `config/packages/knp_paginator.yaml`

```yaml
knp_paginator:
  page_parameter_name: page          # Paramètre URL pour la page
  sort_field_parameter_name: sort    # Paramètre pour trier
  sort_direction_parameter_name: direction  # Ascendant/Descendant
  distinct: true                     # Éviter les doublons
  template:
    pagination: '@KnpPaginator/Pagination/twitter_bootstrap_v5_pagination.html.twig'
    sortable: '@KnpPaginator/Pagination/sortable_link.html.twig'
```

---

## 📚 Utilisation dans les Contrôleurs

### Pattern de Base

```php
// 1. Injecter PaginatorInterface
public function index(
    Request $request,
    PaginatorInterface $paginator,
    CultureRepository $repo
): Response {
    
    // 2. Obtenir la requête (query builder)
    $query = $repo->findAll();  // ou findFiltered(), etc.
    
    // 3. Paginer
    $items = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),  // Page actuelle (défaut: 1)
        10                                     // Items par page
    );
    
    // 4. Passer au template
    return $this->render('...', [
        'items' => $items,
        // ... autres variables
    ]);
}
```

---

### Exemple: CultureController (Déjà Implémenté)

```php
#[Route('/', name: 'app_culture_index', methods: ['GET'])]
public function index(
    Request $request,
    CultureRepository $cultureRepository,
    PaginatorInterface $paginator,
    WeatherService $weatherService
): Response {
    // Obtenir les paramètres de recherche/filtre
    $search         = $request->query->get('search');
    $filterType     = $request->query->get('type');
    $sortSuperficie = $request->query->get('sort');

    // Construire la requête en fonction des filtres
    $query = match (true) {
        (bool) $search         => $cultureRepository->search($search),
        (bool) $filterType     => $cultureRepository->filterByType($filterType),
        (bool) $sortSuperficie => $cultureRepository->orderBySuperficie($sortSuperficie),
        default                => $cultureRepository->findAll(),
    };

    // 📄 PAGINATION KnpPaginator
    $cultures = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),  // Récupère page=1, 2, 3, etc.
        10                                     // 10 items par page
    );

    return $this->render('culture/culture_index.html.twig', [
        'cultures'   => $cultures,  // Objet PaginationInterface
        'stats'      => $stats,
        'search'     => $search,
        'filterType' => $filterType,
    ]);
}
```

---

### Exemple: DiagnosticController (Déjà Implémenté)

```php
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

    // Construire la requête filtrée
    $query = $repo->findFiltered($search, $cultureId, $dateDebut, $dateFin);

    // 📄 PAGINATION
    $diagnostics = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        8  // 8 items par page pour les diagnostics
    );

    return $this->render('diagnostic/diagnostic_index.html.twig', [
        'diagnostics'   => $diagnostics,  // Objet paginé
        'cultures'      => $cultureRepo->findAll(),
        'stats'         => $repo->getStats(),
        'search'        => $search,
        'filterCulture' => $cultureId,
        'dateDebut'     => $dateDebut,
        'dateFin'       => $dateFin,
    ]);
}
```

---

## 🎨 Utilisation dans les Templates Twig

### Afficher les Items Paginés

```twig
{# culture_index.html.twig #}

{% for culture in cultures %}  {# cultures est l'objet pagination #}
    <div class="card">
        <h3>{{ culture.nom }}</h3>
        <p>{{ culture.type }}</p>
    </div>
{% endfor %}
```

### Afficher la Pagination

```twig
{# Bootstrap 5 (par défaut) #}
<div class="d-flex justify-content-center mt-5">
    {{ knp_pagination_render(cultures) }}
</div>
```

### Afficher les Informations de Pagination

```twig
{# Nombre total d'items #}
Total: {{ cultures.getTotalItemCount() }} cultures

{# Nombre de pages #}
Pages: {{ cultures.getPageCount() }}

{# Page actuelle #}
Page actuelle: {{ cultures.getCurrentPageNumber() }}

{# Items par page #}
Par page: {{ cultures.getItemNumberPerPage() }}

{# Premier item de la page #}
De: {{ cultures.getFirstItemNumber() }}

{# Dernier item de la page #}
À: {{ cultures.getLastItemNumber() }}
```

### Exemple Complet (Comme dans culture_index.html.twig)

```twig
{% if cultures.getTotalItemCount() == 0 %}
    <div class="text-center py-5">
        <p class="text-muted fs-5">🌿 Aucune culture trouvée.</p>
        <a href="{{ path('app_culture_new') }}" class="btn btn-success mt-2">
            Ajouter une culture
        </a>
    </div>
{% else %}
    {# Afficher les items #}
    <div class="row g-4">
        {% for culture in cultures %}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>{{ culture.nom }}</h5>
                        <p>{{ culture.type }}</p>
                    </div>
                </div>
            </div>
        {% endfor %}
    </div>

    {# Afficher la pagination #}
    <div class="d-flex justify-content-center mt-5">
        {{ knp_pagination_render(cultures) }}
    </div>
{% endif %}
```

---

## 📊 Objets et Méthodes Disponibles

### Objet Pagination (return de `$paginator->paginate()`)

```php
$items = $paginator->paginate(...);

// Itérer
foreach ($items as $item) {
    echo $item->nom;
}

// Informations
$items->getTotalItemCount()      // 150 (total)
$items->getCurrentPageNumber()   // 2 (page actuelle)
$items->getPageCount()           // 15 (nombre de pages)
$items->getItemNumberPerPage()   // 10 (items par page)
$items->getFirstItemNumber()     // 11 (premier item de cette page)
$items->getLastItemNumber()      // 20 (dernier item de cette page)
```

### URL avec Pagination

```
/culture/?page=1                    # Page 1
/culture/?page=2&search=tomate      # Page 2 avec recherche
/culture/?page=1&sort=DESC          # Page 1, tri descendant
/culture/?page=1&type=Fruits        # Page 1, filtré par type
```

---

## 🎯 Configuration Avancée

### Paramètres Personnalisés

Si vous voulez changer le nom du paramètre de page:

```yaml
knp_paginator:
  page_parameter_name: p  # Utiliser ?p=2 au lieu de ?page=2
  sort_field_parameter_name: sort
  sort_direction_parameter_name: dir
```

### Nombre d'Items par Page Dynamique

```php
$itemsPerPage = $request->query->getInt('limit', 10);  // Default 10
$items = $paginator->paginate($query, $page, $itemsPerPage);
```

### Template Personnalisé

```yaml
knp_paginator:
  template:
    pagination: 'pagination/custom.html.twig'  # Template custom
```

---

## 🔍 Exemples Pratiques

### 1. Culture avec Recherche + Pagination

**Contrôleur**:
```php
public function index(Request $request, CultureRepository $repo, PaginatorInterface $paginator)
{
    $search = $request->query->get('search');
    
    // Requête filtrée
    $query = $search 
        ? $repo->createQueryBuilder('c')
            ->where('c.nom LIKE :search')
            ->setParameter('search', "%$search%")
            ->getQuery()
        : $repo->createQueryBuilder('c')->getQuery();
    
    // Pagination
    $cultures = $paginator->paginate($query, $request->query->getInt('page', 1), 10);
    
    return $this->render('culture/index.html.twig', ['cultures' => $cultures, 'search' => $search]);
}
```

**Template**:
```twig
<form method="get">
    <input type="text" name="search" value="{{ search }}">
    <button>Rechercher</button>
</form>

{% for culture in cultures %}
    <div>{{ culture.nom }}</div>
{% endfor %}

{{ knp_pagination_render(cultures) }}
```

### 2. Diagnostic avec Filtres + Pagination

**Contrôleur**:
```php
public function index(Request $request, DiagnosticRepository $repo, PaginatorInterface $paginator)
{
    $cultureId = $request->query->get('culture');
    
    $query = $repo->createQueryBuilder('d');
    
    if ($cultureId) {
        $query->andWhere('d.culture = :culture')
            ->setParameter('culture', $cultureId);
    }
    
    $diagnostics = $paginator->paginate($query->getQuery(), $request->query->getInt('page', 1), 8);
    
    return $this->render('diagnostic/index.html.twig', ['diagnostics' => $diagnostics]);
}
```

---

## 🚀 Optimisation Performance

### N+1 Query Problem

```php
// ❌ Mauvais (N+1 queries)
$items = $paginator->paginate($repo->findAll(), ...);
foreach ($items as $item) {
    echo $item->culture->nom;  // Une query par item!
}

// ✅ Bon (Query jointe)
$query = $repo->createQueryBuilder('d')
    ->leftJoin('d.culture', 'c')
    ->addSelect('c');  // Charger les cultures avec les diagnostics
$items = $paginator->paginate($query->getQuery(), ...);
```

### Caching avec Pagination

```php
// Cache les résultats
$cacheKey = 'cultures_page_' . $request->query->getInt('page', 1);
if ($cache->has($cacheKey)) {
    $items = $cache->get($cacheKey);
} else {
    $items = $paginator->paginate(...);
    $cache->set($cacheKey, $items, 3600);  // 1 heure
}
```

---

## 📋 Checklist d'Implémentation

Pour vos pages de culture et diagnostic:

- [x] KnpPaginatorBundle installé (composer.json)
- [x] Config créée (knp_paginator.yaml)
- [x] DiagnosticController utilise la pagination
- [x] CultureController utilise la pagination
- [x] Templates affichent la pagination
- [ ] Ajouter pagination à d'autres contrôleurs (Produit, Stock, etc.)
- [ ] Personnaliser le template de pagination si besoin
- [ ] Optimiser les requêtes (leftJoin, addSelect)

---

## 🎨 Personnalisation du Template

Créer un template custom pour la pagination:

```twig
{# templates/pagination/custom.html.twig #}
<nav aria-label="Pagination">
    <ul class="pagination justify-content-center">
        {# Page précédente #}
        {% if items.getCurrentPageNumber() > 1 %}
            <li class="page-item">
                <a class="page-link" href="{{ path(route, {..., page: 1}) }}">
                    ❮❮ Première
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="{{ path(route, {..., page: items.getCurrentPageNumber() - 1}) }}">
                    ❮ Précédente
                </a>
            </li>
        {% endif %}

        {# Pages numérotées #}
        {% for page in range(1, items.getPageCount()) %}
            <li class="page-item {% if page == items.getCurrentPageNumber() %}active{% endif %}">
                <a class="page-link" href="{{ path(route, {..., page: page}) }}">
                    {{ page }}
                </a>
            </li>
        {% endfor %}

        {# Page suivante #}
        {% if items.getCurrentPageNumber() < items.getPageCount() %}
            <li class="page-item">
                <a class="page-link" href="{{ path(route, {..., page: items.getCurrentPageNumber() + 1}) }}">
                    Suivante ❯
                </a>
            </li>
            <li class="page-item">
                <a class="page-link" href="{{ path(route, {..., page: items.getPageCount()}) }}">
                    Dernière ❯❯
                </a>
            </li>
        {% endif %}
    </ul>
</nav>
```

---

## 🐛 Dépannage

### Pagination n'affiche rien

```twig
{# Vérifier s'il y a des items #}
{% if items.getTotalItemCount() > 0 %}
    {{ knp_pagination_render(items) }}
{% else %}
    <p>Aucun item</p>
{% endif %}
```

### Problème de performance

```php
// Ajouter avec lazy loading
$query = $repo->createQueryBuilder('c')
    ->leftJoin('c.diagnostics', 'd', 'WITH', 'd.id = (SELECT MAX(d2.id) FROM App\Entity\Diagnostic d2 WHERE d2.culture = c.id)')
    ->addSelect('d');
```

### Query Builder avec Pagination

```php
$qb = $repo->createQueryBuilder('c')
    ->where('c.type = :type')
    ->setParameter('type', 'Fruits')
    ->orderBy('c.nom', 'ASC');

$items = $paginator->paginate($qb, $page, 10);
```

---

## ✨ Résumé

**KnpPaginatorBundle** vous permet de:
- ✅ Afficher de grandes listes sans charger tous les items
- ✅ Naviguer entre pages facilement
- ✅ Filtrer et rechercher avec pagination
- ✅ Trier les colonnes
- ✅ Optimiser les performances

**Vos pages Culture et Diagnostic**:
- ✅ Utilisent déjà la pagination (10 et 8 items par page)
- ✅ Supportent les recherches et filtres
- ✅ Affichent la pagination Bootstrap 5

**Prochaines étapes**:
1. Tester la pagination avec des recherches
2. Ajouter pagination aux autres listes (Produit, Stock, etc.)
3. Optimiser les requêtes avec `leftJoin` et `addSelect`

---

**Status**: ✅ Configuration complète et prête à l'emploi!


