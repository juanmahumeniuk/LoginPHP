<?php
// index.php - El Cerebro (Controller)
require_once 'auth.php';

// Configurar respuesta JSON por defecto para POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Obtener datos. PHP no llena $_POST si el Content-Type es application/json, 
    // pero FormData envía multipart/form-data que PHP sí entiende.

    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (login($email, $password)) {
            echo json_encode(['success' => true, 'message' => 'Login exitoso']);
        } else {
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        }
    } elseif ($action === 'logout') {
        logout();
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }

    exit; // Terminar aquí para no enviar HTML después del JSON
}

// Si es GET, servimos las páginas HTML
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (is_logged_in()) {
        // Usuario logueado -> Dashboard
        readfile(__DIR__ . '/../Frontend/index.html');
    } else {
        // Usuario no logueado -> Login
        readfile(__DIR__ . '/../Frontend/login.html');
    }
}
?>