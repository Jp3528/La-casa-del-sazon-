<?php
// Vercel Serverless PHP Router
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$root = dirname(__DIR__);

// Limpiar ruta
$cleanUri = trim($uri, '/');

if ($cleanUri === '' || $cleanUri === 'index' || $cleanUri === 'index.php') {
    require $root . '/index.php';
    exit;
}

// Comprobar archivo directo .php
if (file_exists($root . '/' . $cleanUri) && is_file($root . '/' . $cleanUri) && str_ends_with($cleanUri, '.php')) {
    require $root . '/' . $cleanUri;
    exit;
}

// Comprobar sin extensión .php (ej: /menu -> /menu.php)
if (file_exists($root . '/' . $cleanUri . '.php')) {
    require $root . '/' . $cleanUri . '.php';
    exit;
}

// Si no coincide, cargar index
require $root . '/index.php';
