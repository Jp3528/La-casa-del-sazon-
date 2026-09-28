<?php
$host = getenv("DB_HOST") ?: "127.0.0.1";
$user = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$db = getenv("DB_NAME") ?: "restaurante";

mysqli_report(MYSQLI_REPORT_OFF);
try {
    $conn = @new mysqli($host, $user, $password, $db);
    if ($conn && $conn->connect_errno) {
        $conn = null;
    } else if ($conn) {
        $conn->set_charset("utf8");
    }
} catch (Throwable $e) {
    $conn = null;
}
?>
