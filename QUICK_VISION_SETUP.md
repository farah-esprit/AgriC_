# ⚡ Guide Rapide - Configuration Vision API (5 min)

## 📋 Résumé Rapide

Vous avez besoin d'une **clé API Google Vision** pour analyser les images.

## 🎯 Obtenir la Clé (2 minutes)

### Étape 1: Ouvrir Google Cloud Console
```
https://console.cloud.google.com/
```
Se connecter avec votre compte Google.

### Étape 2: Sélectionner ou Créer un Projet
- Cliquez sur le sélecteur de projet en haut
- Créez un nouveau projet ou utilisez un existant

### Étape 3: Activer Vision API
```
Aller à: https://console.cloud.google.com/apis/library
Chercher: "Vision API"
Cliquer: "ACTIVER"
```

### Étape 4: Créer une Clé API
```
Aller à: https://console.cloud.google.com/apis/credentials
Cliquer: "+ CRÉER UNE CLÉ D'IDENTIFICATION"
Sélectionner: "Clé API"
COPIER la clé générée
```

## 📝 Ajouter la Clé au Projet (2 minutes)

### Fichier: `.env`
```dotenv
VISION_API_KEY=AIzaSyDxF1g0jG2H4k8L9m0N1p2Q3r4S5t6U7v8W
```

Remplacez `AIzaSyDx...` par votre vraie clé.

### Exemple Complet du Fichier `.env`
```dotenv
APP_ENV=dev
APP_SECRET=edff052ec437c4a2d7a1fb791b89d50b
DATABASE_URL=mysql://root@127.0.0.1:3306/agriconnect_db?serverVersion=8.0.32&charset=utf8mb4
VISION_API_KEY=AIzaSyDxF1g0jG2H4k8L9m0N1p2Q3r4S5t6U7v8W
```

## ✅ Vérifier la Configuration (1 minute)

### Option 1: Tester via PHP
```bash
php test_vision_key.php
```

### Option 2: Via Navigateur
```
http://localhost:8000/culture/analyser-image
```
- Sélectionnez une image
- Cliquez "Analyser l'image"
- Ça doit fonctionner!

## ❓ Problèmes Courants

### Erreur: "Clé API non configurée"
**Cause**: `VISION_API_KEY` n'est pas dans `.env`  
**Solution**: 
1. Ouvrez `.env`
2. Trouvez la ligne `VISION_API_KEY=YOUR_VISION_API_KEY`
3. Remplacez `YOUR_VISION_API_KEY` par votre vraie clé

### Erreur: "Invalid API Key"
**Cause**: La clé est mal recopiée ou expirée  
**Solution**:
1. Recopier la clé depuis Google Cloud Console
2. Vérifier qu'il n'y a pas d'espace blanc
3. Redémarrer le serveur Symfony

### Erreur: "Permission Denied"
**Cause**: L'API Vision n'est pas activée  
**Solution**:
1. Aller à https://console.cloud.google.com/apis/library
2. Chercher "Vision API"
3. Cliquer "ACTIVER"

## 💰 Coût

- ✅ **Gratuit**: Premiers 1000 appels/mois
- 💵 **Au-delà**: ~$1.50 par 1000 appels
- 🔔 **Conseil**: Activez les alertes budgétaires

## 📞 Support

- [Documentation Vision API](https://cloud.google.com/vision/docs)
- Guide complet: `VISION_API_SETUP.md`

---

**⏱️ Temps total**: ~5 minutes  
**Difficulté**: ⭐ Facile

