# 🔑 Guide - Configuration Google Vision API

## 📋 Prérequis
- Un compte Google
- Un projet Google Cloud actif
- L'accès à Google Cloud Console

## 🚀 Étapes pour Obtenir la Clé API

### **Étape 1: Créer/Accéder à Google Cloud Console**
```
1. Allez sur: https://console.cloud.google.com/
2. Connectez-vous avec votre compte Google
3. Créez un nouveau projet ou sélectionnez un existant
```

### **Étape 2: Activer l'API Vision**
```
1. Allez sur: https://console.cloud.google.com/apis/library
2. Cherchez "Vision API"
3. Cliquez sur "Vision API"
4. Cliquez sur le bouton "ACTIVER"
5. Attendez quelques secondes que l'API s'active
```

### **Étape 3: Créer une Clé API**
```
1. Allez sur: https://console.cloud.google.com/apis/credentials
2. Cliquez sur "+ CRÉER UNE CLÉ D'IDENTIFICATION"
3. Sélectionnez "Clé API"
4. Copiez la clé API générée
5. (Optionnel) Restreignez la clé à Vision API uniquement
```

### **Étape 4: Ajouter la Clé au Fichier .env**
```bash
# Ouvrez: .env
VISION_API_KEY=votre_cle_api_ici

# Exemple:
VISION_API_KEY=AIzaSyDxF1g0jG2H4k8L9m0N1p2Q3r4S5t6U7v8W
```

## 🔒 Sécurité - Recommandations

### Développement ✅
- OK d'utiliser dans `.env`
- Gardez-la locale
- Ne la versionnez PAS dans Git

### Production ❌
- NE PAS mettre dans `.env`
- Utilisez les variables d'environnement du serveur
- Utilisez un gestionnaire de secrets (AWS Secrets, Google Secret Manager, etc.)

## 🛡️ Protéger votre Clé

### Ajouter à `.gitignore`
```bash
# .gitignore
.env
.env.local
.env.*.local
```

### Limiter les Permissions de la Clé
1. Allez dans Google Cloud Console
2. Allez dans "Identifiants"
3. Cliquez sur votre clé API
4. Sous "Restrictions d'API", sélectionnez "Vision API"
5. Sous "Restrictions des clés", limitez par:
   - Adresses IP (si possible)
   - Domaines HTTP referrer

## ✅ Vérifier que ça Marche

### Via Curl
```bash
# Test simple - retourne 400 si la clé est valide
curl -X POST \
  "https://vision.googleapis.com/v1/images:annotate?key=YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"requests":[{"image":{"content":""},"features":[{"type":"LABEL_DETECTION"}]}]}'
```

### Via PHP/Symfony
```php
// Dans votre application
echo getenv('VISION_API_KEY'); // Devrait afficher votre clé
```

### Via Symfony Console
```bash
php bin/console config:dump-reference VISION_API_KEY
php bin/console debug:config VISION_API_KEY
```

## 🐛 Dépannage

### Erreur: "Clé API Google Vision non configurée"
**Solution:**
1. Vérifiez que `VISION_API_KEY` est dans `.env`
2. Vérifiez que la valeur n'est PAS `YOUR_VISION_API_KEY`
3. Vérifiez que la clé n'a pas de guillemets supplémentaires
4. Redémarrez le serveur Symfony

### Erreur: "Invalid API Key"
**Solution:**
1. Vérifiez que la clé est correcte (copie-colle)
2. Vérifiez que l'API Vision est ACTIVÉE
3. Vérifiez que le projet Google Cloud est correct
4. Attendez quelques minutes (propagation)

### Erreur: "Permission denied" ou "Quota exceeded"
**Solution:**
1. Vérifiez le quota sur Google Cloud Console
2. Attendez avant de faire d'autres appels
3. Mettez à niveau votre compte si nécessaire

## 📞 Support

- [Documentation Google Vision API](https://cloud.google.com/vision/docs)
- [Codes d'erreur Google Cloud](https://cloud.google.com/docs/authentication/troubleshooting)
- [Stack Overflow - vision-api tag](https://stackoverflow.com/questions/tagged/vision-api)

## 💡 Astuces

### Limite Gratuite
- Google Cloud offre **1000 appels/mois gratuits**
- Au-delà: ~$1.50 par 1000 appels
- Activez les alertes budgétaires!

### Variables d'Environnement Alternatives
```bash
# .env.local (non versionnée)
VISION_API_KEY=AIzaSyDxF1g0jG2H4k8L9m0N1p2Q3r4S5t6U7v8W

# .env (template avec exemple)
VISION_API_KEY=YOUR_VISION_API_KEY
```

---

**Date**: 2026-04-16  
**Version**: 1.0

