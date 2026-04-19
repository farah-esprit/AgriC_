"""
AgriConnect — Face Recognition Micro-service
Python + FastAPI + DeepFace

Ce serveur expose deux endpoints :
  POST /verify  → compare deux images (base64) et retourne true/false
  GET  /health  → vérifie que le serveur est bien démarré
"""

import base64
import os
import tempfile
import logging
from io import BytesIO

import cv2
import numpy as np
from deepface import DeepFace
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

# --- Configuration du logging ---
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("face_service")

app = FastAPI(
    title="AgriConnect Face Recognition Service",
    description="Micro-service de reconnaissance faciale (DeepFace + OpenCV)",
    version="1.0.0"
)

# Autoriser les requêtes depuis Symfony (localhost:8000)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000", "http://localhost:8000"],
    allow_methods=["*"],
    allow_headers=["*"],
)


# --- Modèles de données (Pydantic) ---
class VerifyRequest(BaseModel):
    """
    Deux images envoyées en base64 (format dataURL ou raw base64).
    - reference_image : photo de référence (sauvegardée lors de l'inscription)
    - live_image      : photo capturée en temps réel depuis la webcam
    """
    reference_image: str  # base64
    live_image: str       # base64
    threshold: float = 0.6  # Seuil de correspondance (0=identique, 1=très différent)


class VerifyResponse(BaseModel):
    verified: bool
    distance: float
    threshold: float
    model: str
    message: str


# --- Utilitaires ---
def decode_base64_to_file(b64_string: str, suffix: str = ".jpg") -> str:
    """
    Décode une image base64 (avec ou sans header dataURL) et l'écrit dans un fichier temporaire.
    Retourne le chemin du fichier temporaire.
    """
    # Supprimer le header "data:image/...;base64," si présent
    if "," in b64_string:
        b64_string = b64_string.split(",")[1]

    img_data = base64.b64decode(b64_string)
    img_array = np.frombuffer(img_data, np.uint8)
    img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)

    if img is None:
        raise ValueError("Impossible de décoder l'image fournie.")

    # Sauvegarder dans un fichier temporaire
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=suffix)
    cv2.imwrite(tmp.name, img)
    return tmp.name


# --- Routes ---
@app.get("/health")
def health_check():
    """Vérifie que le service est opérationnel."""
    return {"status": "ok", "service": "AgriConnect Face Recognition"}


@app.post("/verify", response_model=VerifyResponse)
def verify_face(request: VerifyRequest):
    """
    Compare la photo de référence (inscription) avec la photo live (webcam).
    Retourne verified=True si c'est la même personne.
    """
    ref_path = None
    live_path = None

    try:
        logger.info("Décodage des images reçues...")
        ref_path  = decode_base64_to_file(request.reference_image)
        live_path = decode_base64_to_file(request.live_image)

        logger.info(f"Analyse DeepFace en cours (seuil={request.threshold})...")

        result = DeepFace.verify(
            img1_path=ref_path,
            img2_path=live_path,
            model_name="OpenFace",    # Modèle ultra léger (15Mo) garanti de télécharger!
            distance_metric="cosine",
            enforce_detection=True,  
            detector_backend="opencv"
        )

        verified = result.get("verified", False)
        distance = round(result.get("distance", 1.0), 4)

        logger.info(f"Résultat : verified={verified}, distance={distance}")

        return VerifyResponse(
            verified=verified,
            distance=distance,
            threshold=request.threshold,
            model="OpenFace",
            message="Visage reconnu ✅" if verified else "Visage non reconnu ❌"
        )

    except ValueError as e:
        logger.error(f"Erreur d'image : {e}")
        raise HTTPException(status_code=400, detail=str(e))

    except Exception as e:
        error_msg = str(e)
        logger.error(f"Erreur DeepFace : {error_msg}")

        # Cas où aucun visage n'est détecté dans l'image
        if "Face could not be detected" in error_msg or "No face detected" in error_msg:
            return VerifyResponse(
                verified=False,
                distance=1.0,
                threshold=request.threshold,
                model="OpenFace",
                message="Aucun visage détecté dans l'une des images."
            )

        raise HTTPException(status_code=500, detail=f"Erreur interne : {error_msg}")

    finally:
        # Nettoyage des fichiers temporaires
        for path in [ref_path, live_path]:
            if path and os.path.exists(path):
                try:
                    os.unlink(path)
                except Exception:
                    pass
