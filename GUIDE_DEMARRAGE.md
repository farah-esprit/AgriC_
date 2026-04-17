# 🚀 GUIDE DE DÉMARRAGE - API Diagnostic IA

## ✅ Prérequis

- [x] PHP 8.2+
- [x] Composer
- [x] Symfony 6.4+
- [x] MySQL/MariaDB
- [x] GEMINI_API_KEY configurée dans `.env`

---

## 🔧 Installation Initiale (Une seule fois)

### 1. Installer les dépendances
```bash
cd "C:\Users\Admin\Desktop\Esprit\1er\SYMFONY\Agric"
composer install
```

### 2. Configurer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 3. Vérifier la clé API Gemini
```bash
# Windows PowerShell
Select-String -Path .env -Pattern GEMINI_API_KEY

# Ou Linux/Mac
grep GEMINI_API_KEY .env
```

Doit afficher quelque chose comme:
```
GEMINI_API_KEY=VOTRE_CLE_ICI
```

---

## 🎯 Démarrage pour le Développement

### Terminal 1: Serveur Symfony
```bash
cd "C:\Users\Admin\Desktop\Esprit\1er\SYMFONY\Agric"
symfony server:start
```

Vous devez voir:
```
[OK] Web Server listening on http://127.0.0.1:8000

# ...ou
Starting Web Server...
Web server listening on http://localhost:8000
```

### Ouvrir dans le navigateur
```
http://localhost:8000
```

---

## 🧪 Tester l'API Diagnostic

### Méthode 1: Via le Formulaire Web
1. Aller à: `http://localhost:8000/diagnostic/new`
2. Remplir les symptômes
3. Cliquer sur "Analyser avec l'IA Gemini"
4. Attendre la réponse (5-30 secondes)

### Méthode 2: Avec cURL (Windows)
```powershell
$ApiUrl = "http://localhost:8000/diagnostic/api/ai"
$body = @{
    symptomes = "Feuilles jaunissantes avec taches brunes, tige molle, champignons blancs"
    idCulture = 1
    infos = "Conditions humides"
} | ConvertTo-Json

Invoke-WebRequest -Uri $ApiUrl -Method POST -ContentType "application/json" -Body $body
```

### Méthode 3: Avec PowerShell Script
```bash
powershell -ExecutionPolicy Bypass -File test_api_diagnostic.ps1
```

---

## 📊 Vérifier que l'API Fonctionne

### Logs en Temps Réel
```bash
# Terminal 2 (ou tab séparé)
cd "C:\Users\Admin\Desktop\Esprit\1er\SYMFONY\Agric"
tail -f var/log/dev.log
```

Cherchez ces messages de succès:
```
[INFO] Appel API Gemini, ['url' => 'https://generativelanguage.googleapis.com...']
[INFO] Réponse API Gemini, ['status' => 200]
[INFO] Diagnostic IA analysé avec succès
```

### Erreurs Courantes
| Message | Cause | Solution |
|---------|-------|----------|
| `Clé API Gemini non configurée` | .env absent/invalide | Vérifier GEMINI_API_KEY dans .env |
| `Erreur réseau` | API Gemini indisponible | Vérifier connexion Internet |
| `Erreur de connexion à l'API` | Firewall/Proxy | Vérifier les paramètres réseau |
| `JSON invalide` | Réponse Gemini mal formée | Relancer l'analyse |

---

## 🔍 Debugger les Problèmes

### 1. Vérifier que Symfony démarre bien
```bash
symfony server:status
# ou
symfony server:start -v  # Mode verbose
```

### 2. Vérifier les tables DB
```bash
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM diagnostic"
```

### 3. Vérifier la configuration des services
```bash
php bin/console debug:container | grep -i diagnostic
php bin/console debug:container | grep -i gemini
```

### 4. Tester la clé API directement
```bash
curl "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=YOUR_KEY" \
  -H "Content-Type: application/json" \
  -d '{"contents":[{"parts":[{"text":"Hello"}]}]}'
```

---

## 📈 Performance et Optimisation

### Temps d'Exécution Normal
- ⚡ Requête API: < 1 seconde
- ⏱️ Analyse Gemini: 5-15 secondes
- 🎬 Animation affichage: < 1 seconde
- **Total**: 6-16 secondes

### Si ça prend plus de 30 secondes
- Vérifier la vitesse Internet
- Vérifier les logs pour les erreurs
- Relancer l'analyse
- Vérifier la clé API (budget atteint?)

---

## 🔐 Sécurité

⚠️ **IMPORTANT**: Ne jamais commiter la clé API!

```bash
# Vérifier que .env n'est pas suivi
git status | grep ".env"
# Ne doit rien afficher (ou afficher .env.local)

# .env.local est ignoré (confidentiel)
# .env.example est versionné (modèle)
```

---

## 🚀 Mise en Production

### Avant le déploiement:

```bash
# 1. Mode prod
APP_ENV=prod symfony server:start

# 2. Cache vide
php bin/console cache:clear --env=prod

# 3. Assets compilés
php bin/console asset-map:compile

# 4. Variables d'environnement
# Définir GEMINI_API_KEY sur le serveur de prod
```

### Variables Requises en Production:
```
APP_ENV=prod
APP_SECRET=(généré)
DATABASE_URL=(production)
GEMINI_API_KEY=(clé API prod)
```

---

## 📞 Contacts & Support

**Documentation Officielle:**
- Symfony: https://symfony.com/doc/6.4/
- Google Gemini: https://ai.google.dev/docs
- PlantNet: https://my.plantnet.org/

**Logs Utiles:**
- Dev: `var/log/dev.log`
- Prod: `var/log/prod.log`

---

## ✨ Résumé des Corrections

| Avant | Après | Bénéfice |
|-------|-------|----------|
| Paramètre `informationsComplementaires` | Paramètre `infos` | ✅ Données correctement transmises |
| Pas de validation | Validation + try-catch | ✅ Erreurs explicites |
| Messages vagues | Codes HTTP appropriés | ✅ Debugging facile |

---

**Status: ✅ Prêt pour les tests!**

Commencez par: `symfony server:start` puis accédez à `http://localhost:8000/diagnostic/new`

