<?php
$host = getenv("DB_HOST") ?: "127.0.0.1";
$user = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$db = getenv("DB_NAME") ?: "restaurante";

$conn = new mysqli($host, $user, $password, $db);
$conn->set_charset("utf8");
?>
