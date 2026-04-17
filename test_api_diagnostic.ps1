# Test Script for AI Diagnostic API (Windows PowerShell)
# Usage: powershell -ExecutionPolicy Bypass -File test_api_diagnostic.ps1

$ApiUrl = "http://localhost:8000/diagnostic/api/ai"

Write-Host "🧪 Test 1: Symptômes valides avec culture" -ForegroundColor Cyan
$body1 = @{
    symptomes = "Feuilles jaunissantes avec taches brunes, tige molle à la base, champignons blancs"
    idCulture = 1
    infos = "Conditions humides, stade floraison"
} | ConvertTo-Json

Invoke-WebRequest -Uri $ApiUrl -Method POST -ContentType "application/json" -Body $body1 | ConvertTo-Json
Write-Host ""

Write-Host "`n🧪 Test 2: Symptômes sans culture (optionnel)" -ForegroundColor Cyan
$body2 = @{
    symptomes = "Feuilles desséchées, présence de pucerons, croissance ralentie"
} | ConvertTo-Json

Invoke-WebRequest -Uri $ApiUrl -Method POST -ContentType "application/json" -Body $body2 | ConvertTo-Json
Write-Host ""

Write-Host "`n🧪 Test 3: Symptômes trop courts (doit échouer)" -ForegroundColor Yellow
$body3 = @{
    symptomes = "Court"
} | ConvertTo-Json

try {
    Invoke-WebRequest -Uri $ApiUrl -Method POST -ContentType "application/json" -Body $body3 | ConvertTo-Json
} catch {
    Write-Host "Erreur attendue: $_" -ForegroundColor Red
}
Write-Host ""

Write-Host "`n🧪 Test 4: Symptômes vides (doit échouer)" -ForegroundColor Yellow
$body4 = @{} | ConvertTo-Json

try {
    Invoke-WebRequest -Uri $ApiUrl -Method POST -ContentType "application/json" -Body $body4 | ConvertTo-Json
} catch {
    Write-Host "Erreur attendue: $_" -ForegroundColor Red
}

Write-Host "`n✅ Tests terminés!" -ForegroundColor Green

