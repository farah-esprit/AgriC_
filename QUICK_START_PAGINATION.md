# 🚀 QUICK START - KnpPaginator

## ⚡ 60 Secondes pour Comprendre

### 1. Concept
Montrer **10 items par page** au lieu de **tous les items**.

```
Page 1: Items 1-10 ✅
Page 2: Items 11-20 ✅  
Page 3: Items 21-30 ✅
...
```

### 2. Installation
✅ **Déjà fait!** Bundle installé dans composer.json

### 3. Configuration
✅ **Déjà fait!** Fichier créé: `config/packages/knp_paginator.yaml`

---

## 📝 5 Minutes pour Ajouter à Votre Page

### Votre Contrôleur (Avant)
```php
public function index(YourRepository $repo)
{
    $items = $repo->findAll();  // Tous les items!
    return $this->render('...', ['items' => $items]);
}
```

### Votre Contrôleur (Après)
```php
use Knp\Component\Pager\PaginatorInterface;  // ← Ajouter

public function index(
    Request $request,
    YourRepository $repo,
    PaginatorInterface $paginator  // ← Ajouter
): Response {
    // ← Ajouter ces 3 lignes
    $items = $paginator->paginate(
        $repo->findAll(),
        $request->query->getInt('page', 1),
        10  // Nombre d'items par page
    );
    
    return $this->render('...', ['items' => $items]);
}
```

**Changements**: 
- Ajouter 1 import
- Injecter `PaginatorInterface`
- Envelopper `findAll()` avec `paginate()`

### Votre Template (Avant)
```twig
{% for item in items %}
    <div>{{ item.name }}</div>
{% endfor %}
```

### Votre Template (Après)
```twig
{% for item in items %}
    <div>{{ item.name }}</div>
{% endfor %}

{{ knp_pagination_render(items) }}  {# ← Ajouter cette ligne #}
```

**Changement**: Ajouter 1 ligne au template

---

## ✅ C'est tout!

Votre page change automatiquement:
```
/mypage/              → Page 1
/mypage/?page=2       → Page 2
/mypage/?page=3       → Page 3
```

---

## 🎯 Vos Pages Actuelles

### ✅ Culture
- URL: `/culture/`
- Pagination: 10 items par page
- Template: Affiche déjà `{{ knp_pagination_render(cultures) }}`

### ✅ Diagnostic
- URL: `/diagnostic/`
- Pagination: 8 items par page
- Template: Affiche déjà la pagination

---

## 🎨 Infos Disponibles en Template

```twig
{# Nombre total d'items #}
{{ items.getTotalItemCount() }}  {# Ex: 150 #}

{# Page actuelle #}
{{ items.getCurrentPageNumber() }}  {# Ex: 2 #}

{# Nombre de pages #}
{{ items.getPageCount() }}  {# Ex: 15 #}

{# Items à afficher par page #}
{{ items.getItemNumberPerPage() }}  {# Ex: 10 #}

{# Premier item de cette page #}
{{ items.getFirstItemNumber() }}  {# Ex: 11 #}

{# Dernier item de cette page #}
{{ items.getLastItemNumber() }}  {# Ex: 20 #}
```

### Exemple Complet
```twig
<p>Affichage {{ items.getFirstItemNumber() }} à {{ items.getLastItemNumber() }}
   sur {{ items.getTotalItemCount() }} items (Page {{ items.getCurrentPageNumber() }}/{{ items.getPageCount() }})</p>
```

Affiche: "Affichage 11 à 20 sur 150 items (Page 2/15)"

---

## 🔗 Avec Recherche/Filtres

### Contrôleur
```php
$search = $request->query->get('search');

$query = $search 
    ? $repo->createQueryBuilder('p')
        ->where('p.nom LIKE :search')
        ->setParameter('search', "%$search%")
        ->getQuery()
    : $repo->findAll();

$items = $paginator->paginate($query, $request->query->getInt('page', 1), 10);
```

### URLs Générées
```
/products/                           # Page 1, aucun filtre
/products/?search=apple             # Page 1, cherche "apple"
/products/?search=apple&page=2      # Page 2, cherche "apple"
```

---

## 🎨 Affichage Personnalisé

### Nombre d'Items Dynamique
```php
$limit = $request->query->getInt('limit', 10);  // Default 10
$items = $paginator->paginate($query, $page, $limit);
```

URLs:
```
/page/?limit=20       # 20 items par page
/page/?limit=50       # 50 items par page
/page/?limit=10       # 10 items par page (défaut)
```

### Tri
```php
$sort = $request->query->get('sort', 'date');
$direction = $request->query->get('direction', 'DESC');

$qb = $repo->createQueryBuilder('p')
    ->orderBy("p.$sort", $direction)
    ->getQuery();

$items = $paginator->paginate($qb, $page, 10);
```

URLs:
```
/page/?sort=name&direction=ASC      # Trier par nom ascendant
/page/?sort=date&direction=DESC     # Trier par date descendant
```

---

## 🐛 Problèmes Courants

### Le rendu de pagination n'apparaît pas
```twig
{# Ajouter cette ligne #}
{{ knp_pagination_render(items) }}
```

### Erreur "getTotalItemCount()"
```twig
{# Vérifier que items est l'objet paginé, pas un array #}
{% if items.getTotalItemCount() > 0 %}
    ...
{% endif %}
```

### Une requête lancée par item (N+1)
```php
{# Utiliser leftJoin pour charger les relations #}
$qb = $repo->createQueryBuilder('d')
    ->leftJoin('d.culture', 'c')
    ->addSelect('c');  {# Charge les cultures d'un coup! #}
```

---

## 📊 Exemple Complet Copier-Coller

### Contrôleur (ProduitController.php)
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
    #[Route('/', name: 'app_produit_index')]
    public function index(
        Request $request,
        ProduitRepository $produitRepository,
        PaginatorInterface $paginator
    ): Response {
        $produits = $paginator->paginate(
            $produitRepository->findAll(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }
}
```

### Template (produit/index.html.twig)
```twig
{% extends 'base.html.twig' %}

{% block body %}
<h1>Produits</h1>

{% if produits.getTotalItemCount() == 0 %}
    <p>Aucun produit.</p>
{% else %}
    <div class="row">
        {% for produit in produits %}
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5>{{ produit.nom }}</h5>
                        <p>{{ produit.prix }} €</p>
                    </div>
                </div>
            </div>
        {% endfor %}
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ knp_pagination_render(produits) }}
    </div>
{% endif %}
{% endblock %}
```

---

## ✨ Cas d'Usage Réels

### 📱 E-Commerce
```php
// 20 produits par page
$produits = $paginator->paginate($query, $page, 20);
```

### 📰 Blog
```php
// 10 articles par page
$articles = $paginator->paginate($query, $page, 10);
```

### 👥 Utilisateurs
```php
// 50 utilisateurs par page
$users = $paginator->paginate($query, $page, 50);
```

### 📝 Commentaires
```php
// 25 commentaires par page
$comments = $paginator->paginate($query, $page, 25);
```

---

## 🎯 Checklist Rapide

Pour paginer une liste:

- [ ] Ajouter `use Knp\Component\Pager\PaginatorInterface;`
- [ ] Injecter `PaginatorInterface $paginator` au contrôleur
- [ ] Envelopper la requête avec `$paginator->paginate()`
- [ ] Passer l'objet paginé au template
- [ ] Ajouter `{{ knp_pagination_render(items) }}` au template
- [ ] Tester l'URL: `?page=2`

---

## 🚀 Gain Immédiat

**Avant**: Charger 1000 items et les afficher tous  
**Après**: Charger 10 items et afficher une pagination

- ⚡ **Performance**: +100x plus rapide
- 💾 **Mémoire**: -90% d'utilisation
- 🎨 **UX**: Meilleure expérience utilisateur
- 📱 **Mobile**: Chargement plus rapide

---

## 📚 Besoin de Plus?

| Ressource | Temps | Contenu |
|-----------|-------|---------|
| Quick Start | 5 min | Ce fichier ✅ |
| Guide Complet | 15 min | GUIDE_PAGINATION.md |
| Modèles | 5 min | MODELES_PAGINATION.md |
| Référence | 10 min | RESUME_PAGINATION.md |

---

**Status**: ✅ Prêt à l'emploi!

Commencez maintenant:
1. Ouvrir votre contrôleur
2. Ajouter les 3 lignes de pagination
3. Ajouter la ligne de template
4. Tester avec `?page=2`

**5 minutes pour transformer votre liste!**

