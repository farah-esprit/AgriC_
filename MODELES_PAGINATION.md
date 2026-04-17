# 🎯 MODÈLES D'IMPLÉMENTATION - Pagination KnpPaginator

## 📌 Pour Vos Contrôleurs

Utilisez ces modèles pour ajouter la pagination à d'autres listes.

---

## 1️⃣ Modèle Simple (Sans Filtres)

### Contrôleur

```php
<?php
namespace App\Controller;

use App\Repository\ProduitRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProduitRepository $produitRepository,
        PaginatorInterface $paginator
    ): Response {
        // 1. Obtenir la requête
        $query = $produitRepository->findAll();

        // 2. Paginer
        $produits = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),  // Page courante
            10                                     // 10 items par page
        );

        // 3. Rendre le template
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }
}
```

### Template

```twig
{% extends 'base.html.twig' %}

{% block title %}Produits{% endblock %}

{% block body %}
<section>
    <h1>Produits</h1>
    
    {% if produits.getTotalItemCount() == 0 %}
        <p>Aucun produit.</p>
    {% else %}
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {% for produit in produits %}
                    <tr>
                        <td>{{ produit.nom }}</td>
                        <td>{{ produit.prix }} €</td>
                        <td>
                            <a href="{{ path('app_produit_show', {'id': produit.id}) }}">Voir</a>
                        </td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>

        {# Pagination #}
        <div class="d-flex justify-content-center mt-4">
            {{ knp_pagination_render(produits) }}
        </div>
    {% endif %}
</section>
{% endblock %}
```

---

## 2️⃣ Modèle Avancé (Avec Recherche)

### Contrôleur

```php
<?php
namespace App\Controller;

use App\Repository\StockRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock')]
class StockController extends AbstractController
{
    #[Route('/', name: 'app_stock_index', methods: ['GET'])]
    public function index(
        Request $request,
        StockRepository $stockRepository,
        PaginatorInterface $paginator
    ): Response {
        // 1. Obtenir les paramètres de recherche
        $search = $request->query->get('search');
        $status = $request->query->get('status');

        // 2. Construire la requête en fonction des filtres
        if ($search || $status) {
            $query = $stockRepository->createQueryBuilder('s');
            
            if ($search) {
                $query->andWhere('s.nom LIKE :search')
                    ->setParameter('search', "%$search%");
            }
            
            if ($status) {
                $query->andWhere('s.status = :status')
                    ->setParameter('status', $status);
            }
            
            $query = $query->getQuery();
        } else {
            $query = $stockRepository->findAll();
        }

        // 3. Paginer
        $stocks = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15  // 15 items par page pour stock
        );

        // 4. Rendre
        return $this->render('stock/index.html.twig', [
            'stocks' => $stocks,
            'search' => $search,
            'status' => $status,
        ]);
    }
}
```

### Template

```twig
{% extends 'base.html.twig' %}

{% block title %}Stock{% endblock %}

{% block body %}
<section>
    <h1>Gestion du Stock</h1>
    
    {# Formulaire de recherche #}
    <form method="get" class="mb-4">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" 
                       placeholder="Rechercher..." value="{{ search ?? '' }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Tous --</option>
                    <option value="actif" {% if status == 'actif' %}selected{% endif %}>Actif</option>
                    <option value="inactif" {% if status == 'inactif' %}selected{% endif %}>Inactif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Rechercher</button>
            </div>
        </div>
    </form>

    {# Tableau #}
    {% if stocks.getTotalItemCount() == 0 %}
        <p class="text-muted">Aucun stock trouvé.</p>
    {% else %}
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Quantité</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {% for stock in stocks %}
                        <tr>
                            <td>{{ stock.nom }}</td>
                            <td>{{ stock.quantite }}</td>
                            <td>
                                <span class="badge bg-{{ stock.status == 'actif' ? 'success' : 'danger' }}">
                                    {{ stock.status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ path('app_stock_edit', {'id': stock.id}) }}" class="btn btn-sm btn-warning">
                                    Modifier
                                </a>
                            </td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>

        {# Pagination #}
        <div class="d-flex justify-content-center mt-5">
            {{ knp_pagination_render(stocks) }}
        </div>

        {# Infos pagination #}
        <p class="text-center text-muted mt-3">
            Affichage {{ stocks.getFirstItemNumber() }} à {{ stocks.getLastItemNumber() }} 
            sur {{ stocks.getTotalItemCount() }} items
        </p>
    {% endif %}
</section>
{% endblock %}
```

---

## 3️⃣ Modèle Complexe (Avec Tri + Filtres Multiples)

### Contrôleur

```php
<?php
namespace App\Controller;

use App\Repository\CommandeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(
        Request $request,
        CommandeRepository $commandeRepository,
        PaginatorInterface $paginator
    ): Response {
        // Paramètres
        $search = $request->query->get('search');
        $status = $request->query->get('status');
        $dateDebut = $request->query->get('dateDebut');
        $dateFin = $request->query->get('dateFin');
        $sortBy = $request->query->get('sort', 'date');
        $direction = $request->query->get('direction', 'DESC');

        // Construire la requête
        $qb = $commandeRepository->createQueryBuilder('c');

        // Filtres
        if ($search) {
            $qb->andWhere('c.numero LIKE :search')
                ->setParameter('search', "%$search%");
        }

        if ($status) {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        if ($dateDebut) {
            $qb->andWhere('c.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($dateDebut));
        }

        if ($dateFin) {
            $qb->andWhere('c.date <= :dateFin')
                ->setParameter('dateFin', new \DateTime($dateFin));
        }

        // Tri
        $qb->orderBy("c.$sortBy", in_array($direction, ['ASC', 'DESC']) ? $direction : 'DESC');

        // Pagination
        $commandes = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'search'    => $search,
            'status'    => $status,
            'dateDebut' => $dateDebut,
            'dateFin'   => $dateFin,
            'sort'      => $sortBy,
            'direction' => $direction,
        ]);
    }
}
```

### Template

```twig
{% extends 'base.html.twig' %}

{% block title %}Commandes{% endblock %}

{% block body %}
<section>
    <h1>Mes Commandes</h1>

    {# Filtres #}
    <form method="get" class="card mb-4 p-4">
        <div class="row g-3">
            <div class="col-md-2">
                <input type="text" name="search" class="form-control" 
                       placeholder="N° commande" value="{{ search ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Status --</option>
                    <option {% if status == 'pending' %}selected{% endif %}>En attente</option>
                    <option {% if status == 'confirmed' %}selected{% endif %}>Confirmée</option>
                    <option {% if status == 'shipped' %}selected{% endif %}>Expédiée</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="dateDebut" class="form-control" value="{{ dateDebut ?? '' }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="dateFin" class="form-control" value="{{ dateFin ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select">
                    <option value="date" {% if sort == 'date' %}selected{% endif %}>Date</option>
                    <option value="total" {% if sort == 'total' %}selected{% endif %}>Total</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </div>
    </form>

    {# Liste #}
    {% if commandes.getTotalItemCount() == 0 %}
        <p class="text-muted">Aucune commande.</p>
    {% else %}
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {% for commande in commandes %}
                        <tr>
                            <td>#{{ commande.numero }}</td>
                            <td>{{ commande.date|date('d/m/Y') }}</td>
                            <td>
                                <span class="badge" 
                                      style="background-color: {% if commande.status == 'shipped' %}#28a745{% elseif commande.status == 'confirmed' %}#ffc107{% else %}#6c757d{% endif %}">
                                    {{ commande.status }}
                                </span>
                            </td>
                            <td>{{ commande.total }} €</td>
                            <td>
                                <a href="{{ path('app_commande_show', {'id': commande.id}) }}" 
                                   class="btn btn-sm btn-info">Détails</a>
                            </td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>

        {# Pagination #}
        <div class="d-flex justify-content-between align-items-center mt-4">
            <p class="text-muted">
                {{ commandes.getFirstItemNumber() }}-{{ commandes.getLastItemNumber() }} 
                / {{ commandes.getTotalItemCount() }}
            </p>
            <div>
                {{ knp_pagination_render(commandes) }}
            </div>
        </div>
    {% endif %}
</section>
{% endblock %}
```

---

## 🚀 Implémentation Rapide (Copier-Coller)

### Étape 1: Modifier le Contrôleur

Ajouter `PaginatorInterface` et utiliser `->paginate()`:

```php
use Knp\Component\Pager\PaginatorInterface;

public function index(Request $request, YourRepository $repo, PaginatorInterface $paginator)
{
    $items = $paginator->paginate(
        $repo->findAll(),  // Ou $repo->createQueryBuilder('a')->getQuery()
        $request->query->getInt('page', 1),
        10  // Items par page
    );
    
    return $this->render('...', ['items' => $items]);
}
```

### Étape 2: Modifier le Template

Remplacer l'affichage de la liste:

```twig
{% for item in items %}
    {# Afficher l'item #}
{% endfor %}

{{ knp_pagination_render(items) }}
```

### Étape 3: C'est tout !

La pagination fonctionne automatiquement. Les URL cambieront:
- `/route/` → `/route/?page=1`
- `/route/?page=2` → Page 2
- Etc.

---

## 📊 Tableau Récapitulatif

| Contrôleur | Items/page | Filtres | Tri | Implémenté |
|-----------|-----------|---------|-----|-----------|
| Culture | 10 | ✅ | Type | ✅ |
| Diagnostic | 8 | ✅ | - | ✅ |
| Produit | 10 | - | - | ❌ |
| Stock | 15 | ✅ | - | ❌ |
| Commande | 20 | ✅ | ✅ | ❌ |
| Forum | 10 | ✅ | Date | ❌ |
| Événement | 8 | ✅ | Date | ❌ |

---

## ✨ Bonnes Pratiques

✅ **À Faire**:
- Paginer toutes les listes > 10 items
- Optimiser avec `leftJoin` et `addSelect`
- Ajouter une recherche aux listes paginées
- Afficher le nombre total d'items

❌ **À Éviter**:
- Charger `findAll()` sans pagination
- Utiliser `LIMIT/OFFSET` au lieu du bundle
- Paginer côté PHP au lieu de la DB
- Oublier d'ajouter `{{ knp_pagination_render() }}`

---

**Status**: ✅ Modèles prêts à copier-coller dans vos contrôleurs!


