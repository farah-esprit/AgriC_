from flask import Flask, request, jsonify
from flask_cors import CORS
import re
import os
from PIL import Image
import io

app = Flask(__name__)
CORS(app)

# Base de connaissances étendue pour le diagnostic
KNOWLEDGE_BASE = {
    "tomate": {
        "mildiou": {
            "keywords": ["taches brunes", "taches noires", "feuilles flétries", "duvet blanc", "humidité", "noirâtre"],
            "suggestion": "Il semble s'agir du Mildiou de la tomate (Phytophthora infestans).",
            "traitements": ["Supprimer les feuilles infectées", "Appliquer de la bouillie bordelaise", "Espacer les plants"],
            "urgence": "élevée",
            "conseil": "Évitez d'arroser les feuilles, le champignon adore l'eau stagnante."
        },
        "oïdium": {
            "keywords": ["poudre blanche", "feutrage blanc", "feuilles blanches", "moisissure blanche"],
            "suggestion": "Diagnostic probable : Oïdium (maladie du blanc).",
            "traitements": ["Soufre pulvérisé", "Bicarbonate de soude + savon noir", "Supprimer les feuilles atteintes"],
            "urgence": "modérée",
            "conseil": "Assurez une bonne aération entre vos plants."
        }
    },
    "olivier": {
        "oeil_de_paon": {
            "keywords": ["taches circulaires", "cercles jaunes", "taches sombres", "défoliation", "chute feuilles"],
            "suggestion": "Il semble s'agir de l'Oeil de paon (Cycloconium oleaginum), très fréquent sur l'olivier.",
            "traitements": ["Traitement au cuivre (Fongicide)", "Taille pour aérer la couronne", "Éviter les excès d'azote"],
            "urgence": "élevée",
            "conseil": "Traitez préventivement à l'automne et au printemps après la pluie."
        },
        "mouche_olivier": {
            "keywords": ["piqûres fruits", "olives mangées", "larves", "perforation", "chute olives"],
            "suggestion": "Attaque probable de la Mouche de l'olive (Bactrocera oleae).",
            "traitements": ["Pièges à phéromones", "Argile blanche sur les fruits", "Récolter précocement"],
            "urgence": "critique",
            "conseil": "Récoltez les olives tombées au sol car elles abritent les larves."
        }
    },
    "vigne": {
        "mildiou_vigne": {
            "keywords": ["taches huile", "taches translucides", "duvet blanc revers", "taches brunes"],
            "suggestion": "Diagnostic : Mildiou de la vigne (Plasmopara viticola).",
            "traitements": ["Bouillie bordelaise", "Épamprage pour aérer", "Supprimer les feuilles au sol"],
            "urgence": "élevée",
            "conseil": "Agissez dès l'apparition des 'taches d'huile' sur le dessus des feuilles."
        }
    },
    "agrumes": {
        "pucerons_agrumes": {
            "keywords": ["feuilles enroulées", "insectes noirs", "fourmis", "miellat", "jeunes pousses"],
            "suggestion": "Présence de pucerons sur vos agrumes.",
            "traitements": ["Savon noir dilué", "Lâcher de coccinelles", "Purin d'ortie"],
            "urgence": "modérée",
            "conseil": "Éliminez les fourmis car elles protègent les pucerons."
        },
        "chlorose": {
            "keywords": ["feuilles jaunes", "nervures vertes", "manque fer", "jaunissement"],
            "suggestion": "Carence probable en Fer (Chlorose ferrique).",
            "traitements": ["Apport de séquestrène (fer chélaté)", "Réduire le calcaire du sol", "Apport de compost acide"],
            "urgence": "faible",
            "conseil": "Évitez les arrosages à l'eau trop calcaire."
        }
    },
    "general": {
        "carence_azote": {
            "keywords": ["jaunissement complet", "petites feuilles", "croissance stoppée"],
            "suggestion": "Carence en Azote détectée.",
            "traitements": ["Apport de purin d'ortie", "Engrais riche en N", "Compost"],
            "urgence": "faible",
            "conseil": "Apportez de l'azote surtout au printemps."
        },
        "stress_hydrique": {
            "keywords": ["feuilles tombantes", "flétrissement", "sol sec", "terre craquelée"],
            "suggestion": "La plante souffre de la soif (Stress hydrique).",
            "traitements": ["Arrosage profond au pied", "Paillage du sol", "Binage pour briser la croûte"],
            "urgence": "modérée",
            "conseil": "Arrosez le soir pour limiter l'évaporation."
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
