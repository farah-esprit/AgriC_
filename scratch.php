<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=agriconnect_db', 'root', '');
$stmt = $db->query('SHOW COLUMNS FROM reclamation');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
