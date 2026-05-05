<?php
$conn = mysqli_connect('127.0.0.1', 'root', '');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$res = mysqli_query($conn, "SHOW DATABASES");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Database'] . "\n";
}
mysqli_close($conn);
