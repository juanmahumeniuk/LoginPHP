<?php
// index.php - El Cerebro (Controller)
require_once 'database.php';
require_once 'auth.php';


// Configurar respuesta JSON por defecto para POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

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
        if (logout()) {
            echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cerrar sesión']);
        }
    } elseif ($action === 'register') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        if (register($email, $password)) {
            echo json_encode(['success' => true, 'message' => 'Registro exitoso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }

    exit; // Terminar aquí para no enviar HTML después del JSON
}


// Si es GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'navigate') {
        // API navigate check (if used by JS)
        header('Content-Type: application/json');
        if (is_logged_in()) {
            echo json_encode(['status' => 'ok', 'page' => 'index.html']);
        } else {
            echo json_encode(['status' => 'ok', 'page' => 'login.html']);
        }
    } elseif ($action === 'getUserCount') {
        header('Content-Type: application/json');
        $count = getUserCount();
        echo json_encode(['status' => 'ok', 'count' => $count]);
    } elseif ($action === 'login_google') {
        // Redirect to Google
        header("Location: " . getGoogleLoginUrl());
        exit;
    } elseif (isset($_GET['code'])) {
        // Handle Google Callback
        $code = $_GET['code'];
        $tokenData = getGoogleAccessToken($code);

        if (isset($tokenData['access_token'])) {
            $userData = getGoogleUserInfo($tokenData['access_token']);

            if (isset($userData['email'])) {
                if (loginWithGoogle($userData['email'])) {
                    // Redirect to dashboard on success
                    header("Location: ../Frontend/index.html");
                    exit;
                }
            }
        }

        // On failure
        header("Location: ../Frontend/login.html?error=google_failed");
        exit;
    } else {
        // Navegación normal del navegador
        if (is_logged_in()) {
            // Usuario logueado -> Dashboard
            readfile(__DIR__ . '/../Frontend/index.html');
        } else {
            // Usuario no logueado -> Login
            readfile(__DIR__ . '/../Frontend/login.html');
        }
    }
}

function getUserCount()
{
    global $conn;
    $query = "SELECT COUNT(*) as count FROM users";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

?>