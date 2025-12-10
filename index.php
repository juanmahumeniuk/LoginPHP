<?php
// index.php - Proxy para Google OAuth
// Google redirige a /index.php, así que redirigimos el tráfico al Backend

// Cambiamos el directorio para que los require dentro de Backend funcionen
chdir(__DIR__ . '/Backend');

// Incluimos el controlador real
require 'index.php';
?>