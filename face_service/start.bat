@echo off
echo ====================================
echo  AgriConnect - Face Recognition API
echo ====================================
echo.

REM Installation des dépendances (à la première utilisation)
echo [1/2] Vérification des dépendances Python...
pip install -r requirements.txt --quiet

echo.
echo [2/2] Démarrage du serveur sur http://localhost:8001
echo.
echo  IMPORTANT : Laissez cette fenêtre ouverte pendant que vous utilisez AgriConnect.
echo  Appuyez sur CTRL+C pour arrêter le serveur.
echo.
echo ====================================

uvicorn main:app --host 127.0.0.1 --port 8001 --reload
