from flask import Flask, request, jsonify
from flask_cors import CORS
import re
import os
from PIL import Image
import io

app = Flask(__name__)
CORS(app)

# Base de connaissances pour le diagnostic
KNOWLEDGE_BASE = {
    "tomate": {
        "mildiou": {
            "keywords": ["taches brunes", "taches noires", "feuilles flétries", "duvet blanc", "humidité"],
            "suggestion": "Il semble s'agir du Mildiou de la tomate (Phytophthora infestans).",
            "traitements": [
                "Supprimer et brûler les feuilles infectées immédiatement",
                "Appliquer de la bouillie bordelaise (solution cuprique)",
                "Améliorer la circulation de l'air et éviter de mouiller le feuillage"
            ],
            "urgence": "élevée",
            "conseil": "Isolez les plants touchés pour éviter la propagation rapide par temps humide."
        },
        "oïdium": {
            "keywords": ["poudre blanche", "feutrage blanc", "feuilles jaunes", "déformation"],
            "suggestion": "Diagnostic probable : Oïdium (maladie du blanc).",
            "traitements": [
                "Pulvériser un mélange de soufre ou de bicarbonate de soude",
                "Supprimer les parties très atteintes",
                "Réduire l'arrosage nocturne"
            ],
            "urgence": "modérée",
            "conseil": "Ne saturez pas l'air autour des plants, le champignon aime l'humidité stagnante."
        },
        "pucerons": {
            "keywords": ["insectes verts", "insectes noirs", "feuilles enroulées", "miellat", "fourmis"],
            "suggestion": "Présence de pucerons détectée.",
            "traitements": [
                "Pulvériser une solution d'eau et de savon noir (5%)",
                "Introduire des prédateurs naturels comme les coccinelles",
                "Rincer le feuillage au jet d'eau"
            ],
            "urgence": "faible",
            "conseil": "Vérifiez le revers des feuilles régulièrement."
        }
    },
    "pomme de terre": {
        "mildiou": {
            "keywords": ["taches brunes", "pourriture", "tiges noires", "humidité"],
            "suggestion": "Mildiou de la pomme de terre détecté.",
            "traitements": [
                "Traitement fongicide à base de cuivre",
                "Récolte anticipée si les tubercules ne sont pas encore atteints",
                "Destruction des fanes infectées"
            ],
            "urgence": "critique",
            "conseil": "Agissez vite, le mildiou peut détruire une récolte entière en quelques jours."
        },
        "doryphore": {
            "keywords": ["scarabée rayé", "larves rouges", "feuilles mangées", "trous"],
            "suggestion": "Attaque de Doryphores.",
            "traitements": [
                "Ramassage manuel des adultes et des larves",
                "Utilisation de bacille de Thuringe (BT)",
                "Rotation des cultures l'année prochaine"
            ],
            "urgence": "élevée",
            "conseil": "Inspectez quotidiennement vos plants."
        }
    },
    "general": {
        "carence_azote": {
            "keywords": ["feuilles jaunes", "croissance lente", "petites feuilles"],
            "suggestion": "Carence probable en Azote (N).",
            "traitements": [
                "Ajouter un engrais riche en azote (purin d'ortie, sang séché)",
                "Apporter du compost bien décomposé"
            ],
            "urgence": "faible",
            "conseil": "Vérifiez le pH de votre sol."
        },
        "manque_eau": {
            "keywords": ["feuilles tombantes", "sol sec", "flétrissement", "jaunissement"],
            "suggestion": "Stress hydrique (manque d'eau).",
            "traitements": [
                "Arrosage régulier au pied de la plante",
                "Paillage pour conserver l'humidité"
            ],
            "urgence": "modérée",
            "conseil": "Arrosez de préférence tôt le matin ou tard le soir."
        }
    }
}

def analyze_disease(symptomes, culture_name):
    symptomes = symptomes.lower()
    culture_name = culture_name.lower() if culture_name else "general"
    
    best_match = None
    max_keywords = 0
    final_disease = None
    
    # Chercher d'abord dans la culture spécifique
    cultures_to_check = [culture_name, "general"] if culture_name in KNOWLEDGE_BASE else ["general"]
    
    for culture in cultures_to_check:
        diseases = KNOWLEDGE_BASE.get(culture, {})
        for disease_id, data in diseases.items():
            count = sum(1 for kw in data["keywords"] if kw in symptomes)
            if count > max_keywords:
                max_keywords = count
                best_match = data
                final_disease = disease_id
                
    if best_match and max_keywords > 0:
        confiance = min(40 + (max_keywords * 15), 98) # Simulation de confiance
        return {
            "suggestion": best_match["suggestion"],
            "maladies": [
                {
                    "nom": final_disease.replace("_", " ").capitalize(),
                    "probabilite": confiance,
                    "description": f"Analyse basée sur {max_keywords} symptôme(s) détecté(s)."
                }
            ],
            "traitements": best_match["traitements"],
            "urgence": best_match["urgence"],
            "conseil": best_match["conseil"],
            "confiance": confiance
        }
    
    # Diagnostic par défaut si rien n'est trouvé
    return {
        "suggestion": "Nous n'avons pas pu identifier la maladie avec certitude. Veuillez consulter un expert.",
        "maladies": [],
        "traitements": ["Surveillez l'évolution des symptômes", "Prenez une photo nette"],
        "urgence": "faible",
        "conseil": "Essayez de décrire les symptômes plus précisément (couleur, texture, localisation).",
        "confiance": 0
    }

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    if not data:
        return jsonify({"error": "No data provided"}), 400
        
    symptomes = data.get('symptomes', '')
    culture = data.get('culture', '')
    
    result = analyze_disease(symptomes, culture)
    return jsonify(result)

@app.route('/predict-image', methods=['POST'])
def predict_image():
    # Récupération des données multipart
    symptomes = request.form.get('symptomes', '')
    culture = request.form.get('culture', '')
    
    if 'image' not in request.files:
        return jsonify({"error": "No image file provided"}), 400
        
    file = request.files['image']
    if file.filename == '':
        return jsonify({"error": "No selected file"}), 400

    try:
        # Analyse de l'image (Simulation avec Pillow)
        img = Image.open(file.stream)
        img_format = img.format
        img_size = img.size
        
        # On lance l'analyse textuelle habituelle
        result = analyze_disease(symptomes, culture)
        
        # On enrichit le résultat avec les infos de l'image
        result["suggestion"] = f"[Analyse Image OK] " + result["suggestion"]
        if result["confiance"] > 0:
            result["confiance"] = min(result["confiance"] + 10, 100)
            
        result["image_info"] = {
            "format": img_format,
            "size": f"{img_size[0]}x{img_size[1]}",
            "status": "Photo traitée avec succès"
        }
        
        return jsonify(result)
        
    except Exception as e:
        return jsonify({"error": f"Erreur traitement image: {str(e)}"}), 500

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"status": "ok", "model": "Local Agriculture NLP"})

if __name__ == '__main__':
    print("Local Agriculture AI Model loading...")
    print("Serving on http://127.0.0.1:5001")
    app.run(host='127.0.0.1', port=5001, debug=True)
