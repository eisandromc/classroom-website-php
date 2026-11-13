<?php
// Roteador opcional para o servidor PHP embutido: php -S localhost:8004 roteador.php
$arquivo = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (is_file($arquivo)) return false;
$rota = trim((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$_GET['rota'] = $rota;
require __DIR__ . '/index.php';
