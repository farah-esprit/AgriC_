<?php
$data = json_decode(file_get_contents('https://nominatim.openstreetmap.org/search?q=tunis&format=json&limit=1'), true);
print_r($data);
