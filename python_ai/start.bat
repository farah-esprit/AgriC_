@echo off
setlocal enabledelayedexpansion

echo ===========================================
echo    AGRICONNECT - SERVEUR IA LOCAL
echo ===========================================
cd /d %~dp0

:: Tester si python ou py est disponible
set PY_CMD=
where python >nul 2>&1 && set PY_CMD=python
if not defined PY_CMD (
    where py >nul 2>&1 && set PY_CMD=py
)

if not defined PY_CMD (
    echo [ERREUR] Python n'est pas installe sur ce PC.
    echo Veuillez l'installer sur : https://www.python.org/downloads/
    pause
    exit /b
)

echo [1/2] Verification des dependances...
!PY_CMD! -m pip install flask flask-cors >nul 2>&1
if !errorlevel! neq 0 (
    echo [ATTENTION] Impossible d'installer les dependances automatiquement.
    echo Essayez de taper : !PY_CMD! -m pip install flask flask-cors
)

echo [2/2] Lancement du serveur IA sur http://127.0.0.1:5001
echo Gardez cette fenetre OUVERTE pendant l'utilisation.
echo -------------------------------------------
!PY_CMD! app.py
pause
