<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'agriconnect_db');
$res = mysqli_query($conn, "SELECT version FROM doctrine_migration_versions");
while ($row = mysqli_fetch_assoc($res)) {
    var_dump($row['version']);
}
