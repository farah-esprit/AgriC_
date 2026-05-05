<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'agriconnect_db');
if (!$conn) die("C1 fail");
echo "=== agriconnect_db ===\n";
$res = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_array($res)) echo $row[0] . "\n";

$conn2 = mysqli_connect('127.0.0.1', 'root', '', 'agriconnect_db(1)');
if ($conn2) {
    echo "=== agriconnect_db(1) ===\n";
    $res = mysqli_query($conn2, "SHOW TABLES");
    while ($row = mysqli_fetch_array($res)) echo $row[0] . "\n";
}
