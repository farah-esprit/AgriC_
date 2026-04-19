import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, r2_score

def generate_mock_data(n_samples=500):
    """
    Génère un jeu de données d'entraînement simulé.
    Représente l'historique des événements avec leur contexte météo et le nombre réel de réclamations.
    """
    np.random.seed(42)
    
    # Variables indépendantes (Features)
    # Capacité max de l'événement (entre 50 et 5000 personnes)
    capacite_max = np.random.randint(50, 5000, n_samples)
    
    # Durée de l'événement en heures (entre 2 et 72 heures)
    duree_heures = np.random.randint(2, 72, n_samples)
    
    # Température en degrés Celsius (entre 5 et 40 degrés)
    temperature = np.random.randint(5, 40, n_samples)
    
    # Précipitations en mm (0 = pas de pluie, >0 = pluie)
    pluie_mm = np.random.exponential(scale=2.0, size=n_samples)
    pluie_mm = np.round(pluie_mm, 1)

    # Création du DataFrame
    df = pd.DataFrame({
        'capacite_max': capacite_max,
        'duree_heures': duree_heures,
        'temperature': temperature,
        'pluie_mm': pluie_mm
    })

    # Variable dépendante (Target) : le nombre de réclamations
    # On simule une logique où :
    # - Plus il y a de monde, plus il y a de réclamations
    # - Les événements plus longs génèrent plus de plaintes
    # - La chaleur extrême (>30) ou la pluie augmentent l'inconfort et donc les plaintes
    
    base_complaints = (df['capacite_max'] * 0.005) + (df['duree_heures'] * 0.1)
    weather_impact = np.where(df['temperature'] > 30, (df['temperature'] - 30) * 0.5, 0) + (df['pluie_mm'] * 1.5)
    
    # Ajout d'un bruit aléatoire pour le réalisme
    noise = np.random.normal(0, 2, n_samples)
    
    complaints = base_complaints + weather_impact + noise
    
    # Le nombre de plaintes ne peut pas être négatif, et c'est un entier
    df['reclamations_reelles'] = np.maximum(0, np.round(complaints)).astype(int)
    
    return df

class EventRiskPredictor:
    def __init__(self):
        # Initialisation du modèle Random Forest Regressor
        self.model = RandomForestRegressor(
            n_estimators=100, 
            max_depth=10, 
            random_state=42
        )
        self.is_trained = False

    def train(self, data):
        """
        Entraîne le modèle Random Forest avec les données fournies.
        """
        print("--- Entraînement du Modèle d'IA ---")
        X = data[['capacite_max', 'duree_heures', 'temperature', 'pluie_mm']]
        y = data['reclamations_reelles']

        # Séparation en jeu d'entraînement et jeu de test
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

        # Apprentissage
        self.model.fit(X_train, y_train)
        self.is_trained = True

        # Évaluation
        y_pred = self.model.predict(X_test)
        mse = mean_squared_error(y_test, y_pred)
        r2 = r2_score(y_test, y_pred)
        
        print(f"✅ Modèle entraîné avec succès.")
        print(f"📊 Précision (R²) : {r2:.2f}")
        print(f"📉 Erreur Quadratique Moyenne (MSE) : {mse:.2f}\n")

    def predict_event_risk(self, capacite_max, duree_heures, temperature, pluie_mm):
        """
        Prédit le nombre de réclamations pour un événement futur.
        """
        if not self.is_trained:
            raise Exception("Le modèle doit être entraîné avant de faire des prédictions.")

        # Formatage des données pour la prédiction
        features = pd.DataFrame([{
            'capacite_max': capacite_max,
            'duree_heures': duree_heures,
            'temperature': temperature,
            'pluie_mm': pluie_mm
        }])

        # Prédiction (retourne un float, on l'arrondit car c'est un volume d'humains)
        prediction = self.model.predict(features)[0]
        return max(0, int(round(prediction)))

def verifier_alerte_risque(prediction_reclamations, capacite_max):
    """
    Logique métier : déclenche une alerte si le ratio de réclamations
    est jugé critique ou si le volume brut dépasse un seuil de saturation des équipes.
    """
    SEUIL_CRITIQUE_VOLUME = 30  # Plus de 30 plaintes satureraient le support local
    SEUIL_CRITIQUE_RATIO = 0.02 # 2% de plaintes est jugé critique pour la réputation

    ratio_plaintes = prediction_reclamations / capacite_max if capacite_max > 0 else 0

    print(f"🔍 Résultat de l'analyse : {prediction_reclamations} plainte(s) estimée(s).")
    
    if prediction_reclamations >= SEUIL_CRITIQUE_VOLUME or ratio_plaintes >= SEUIL_CRITIQUE_RATIO:
        print("⚠️ ALERTE CRITIQUE : Risque de réclamations élevé !")
        print("👉 Action requise : Renforcement immédiat des équipes sur le terrain (sécurité, accueil, support technique).")
        return True
    else:
        print("✅ Niveau de risque acceptable. Équipe standard suffisante.")
        return False

# ==========================================
# EXÉCUTION DU SCRIPT (Mode CLI / Web API)
# ==========================================
if __name__ == "__main__":
    import argparse
    import json
    import sys
    import os

    parser = argparse.ArgumentParser(description="Prédire les réclamations d'un événement")
    parser.add_argument('--json', action='store_true', help='Activer la sortie JSON pour Symfony')
    parser.add_argument('--capacite', type=int, default=2500, help='Capacité max')
    parser.add_argument('--duree', type=int, default=48, help='Durée en heures')
    parser.add_argument('--temp', type=int, default=34, help='Température en °C')
    parser.add_argument('--pluie', type=float, default=0.0, help='Pluie en mm')
    args = parser.parse_args()

    # Si on est en mode JSON, on désactive temporairement le print standard pour ne pas polluer la réponse
    if args.json:
        old_stdout = sys.stdout
        sys.stdout = open(os.devnull, 'w')

    # 1. Génération et Entraînement
    df_train = generate_mock_data(1000)
    predictor = EventRiskPredictor()
    predictor.train(df_train)

    # 2. Prédiction
    volume_estime = predictor.predict_event_risk(
        capacite_max=args.capacite,
        duree_heures=args.duree,
        temperature=args.temp,
        pluie_mm=args.pluie
    )

    # 3. Évaluation
    SEUIL_CRITIQUE_VOLUME = 30
    SEUIL_CRITIQUE_RATIO = 0.02
    ratio_plaintes = volume_estime / args.capacite if args.capacite > 0 else 0
    alerte = (volume_estime >= SEUIL_CRITIQUE_VOLUME) or (ratio_plaintes >= SEUIL_CRITIQUE_RATIO)

    # 4. Sortie
    if args.json:
        # Réactiver la sortie standard pour imprimer le JSON
        sys.stdout.close()
        sys.stdout = old_stdout
        print(json.dumps({
            "prediction_reclamations": volume_estime,
            "alerte_critique": bool(alerte),
            "ratio": round(ratio_plaintes * 100, 2)
        }))
    else:
        print("--- Simulation pour le prochain événement ---")
        print(f"Spécificités : Capacité={args.capacite}, Durée={args.duree}h, Météo={args.temp}°C avec {args.pluie}mm de pluie")
        verifier_alerte_risque(volume_estime, args.capacite)
