
#!/bin/bash
# Test Script for AI Diagnostic API
# Usage: bash test_diagnostic_api.sh

API_URL="http://localhost:8000/diagnostic/api/ai"

echo "🧪 Test 1: Symptômes valides avec culture"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "symptomes": "Feuilles jaunissantes avec taches brunes, tige molle à la base, champignons blancs",
    "idCulture": 1,
    "infos": "Conditions humides, stade floraison"
  }' | jq .

echo -e "\n\n🧪 Test 2: Symptômes sans culture (optionnel)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "symptomes": "Feuilles desséchées, présence de pucerons, croissance ralentie"
  }' | jq .

echo -e "\n\n🧪 Test 3: Symptômes trop courts (doit échouer)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d '{
    "symptomes": "Court"
  }' | jq .

echo -e "\n\n🧪 Test 4: Symptômes vides (doit échouer)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d '{}' | jq .

echo -e "\n\n✅ Tests terminés!"

