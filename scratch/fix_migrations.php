<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'agriconnect_db');
if (!$conn) die("Fail");

$versions = [
    'DoctrineMigrations\\Version20260405005553',
    'DoctrineMigrations\\Version20260405130055',
    'DoctrineMigrations\\Version20260406185124',
    'DoctrineMigrations\\Version20260409175204',
    'DoctrineMigrations\\Version20260420183138'
];

foreach ($versions as $v) {
    mysqli_query($conn, "INSERT IGNORE INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('$v', NOW(), 1)");
    echo "Added $v\n";
}
mysqli_close($conn);
