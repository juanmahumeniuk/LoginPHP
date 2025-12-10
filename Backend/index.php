<?php
// index.php - El Cerebro (Controller)
require_once 'database.php';
require_once 'auth.php';

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurar respuesta JSON por defecto para POST o si se solicita explícitamente API
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    // Si es una navegación normal de GET sin action (o action limpia), no forzamos JSON todavía salvo en los bloques específicos.
    // Pero aquí mezclamos lógica. Mantengamos la estructura original pero interceptemos acciones de tickets.
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents("php://input"), true);

    // Support both Form Data (for login/register original) and JSON (for tickets)
    // If $input is valid JSON, use it. Otherwise fall back to $_POST
    $action = $input['action'] ?? $_POST['action'] ?? '';

    // --- AUTH ACTIONS ---
    if ($action === 'login') {
        $email = $input['email'] ?? $_POST['email'] ?? '';
        $password = $input['password'] ?? $_POST['password'] ?? '';
        if (login($email, $password)) {
            echo json_encode(['success' => true, 'message' => 'Login exitoso']);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        }
    } elseif ($action === 'logout') {
        if (logout()) {
            echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cerrar sesión']);
        }
    } elseif ($action === 'register') {
        $email = $input['email'] ?? $_POST['email'] ?? '';
        $password = $input['password'] ?? $_POST['password'] ?? '';
        if (register($email, $password)) {
            echo json_encode(['success' => true, 'message' => 'Registro exitoso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar']);
        }
    }
    // --- TICKET ACTIONS ---
    elseif ($action === 'create_ticket') {
        // Single ticket creation
        $data = $input; // Asumimos que los datos vienen en el root del JSON o necesitamos extraerlos
        // La lógica original de tickets/index.php usaba el root del JSON.
        // Si usamos 'action' como parámetro, el frontend debe enviarlo.

        $enlace = $data['enlace'] ?? '';
        $descripcion = $data['descripcion'] ?? '';
        $carga_datos = $data['carga_datos'] ?? '';
        $estado = $data['estado'] ?? '';
        $demoras = $data['demoras'] ?? '';
        $datos_faltantes = $data['datos_faltantes'] ?? '';
        $fecha = $data['fecha'] ?? '';

        $stmt = $conn->prepare("INSERT INTO tickets (enlace, descripcion, estado, demoras, carga_datos, datos_faltantes, fecha) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $enlace, $descripcion, $estado, $demoras, $carga_datos, $datos_faltantes, $fecha);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear ticket: ' . $stmt->error]);
        }
        $stmt->close();
    } elseif ($action === 'update_ticket') {
        $id = $input['id'] ?? null;
        if ($id) {
            $descripcion = $input['descripcion'] ?? '';
            $estado = $input['estado'] ?? '';
            $demoras = $input['demoras'] ?? '';
            $carga_datos = $input['carga_datos'] ?? '';
            $datos_faltantes = $input['datos_faltantes'] ?? '';
            $fecha = $input['fecha'] ?? '';

            $stmt = $conn->prepare("UPDATE tickets SET descripcion=?, estado=?, demoras=?, carga_datos=?, datos_faltantes=?, fecha=? WHERE enlace=?");
            $stmt->bind_param("sssssss", $descripcion, $estado, $demoras, $carga_datos, $datos_faltantes, $fecha, $id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Ticket actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Faltan datos (id)']);
        }
    } elseif ($action === 'delete_ticket') {
        $id = $input['id'] ?? '';
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM tickets WHERE enlace = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró el ticket']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta ID']);
        }
    } elseif ($action === 'bulk_create_tickets') {
        // Lógica de guardar_ticket.php
        // Espera un array de tickets en $input['tickets'] o si el body ES el array.
        // Asumiremos que si action está presente, los tickets están en una propiedad 'tickets' O
        // adaptamos el frontend para que envíe { action: 'bulk...', tickets: [...] }

        $tickets = $input['tickets'] ?? [];
        if (empty($tickets) && is_array($input) && !isset($input['action'])) {
            // Fallback si pasaban el array directo, pero aquí requerimos 'action' para routing.
            // Asumimos formato nuevo: { "action": "bulk_create_tickets", "tickets": [ ... ] }
        }

        $stmt = $conn->prepare("INSERT INTO tickets (enlace, descripcion, carga_datos, estado, demoras, datos_faltantes, fecha) VALUES (?, ?, ?, ?, ?, ?, ?)");

        $success = true;
        foreach ($tickets as $ticket) {
            // Mapeo flexible de keys (mayúsculas o minúsculas)
            $enlace = $ticket['ENLACE'] ?? $ticket['enlace'] ?? '';
            $descripcion = $ticket['DESCRIPCIÓN'] ?? $ticket['descripcion'] ?? '';
            $cargaDeDatos = $ticket['CARGA DE DATOS'] ?? $ticket['carga_datos'] ?? '';
            $estado = $ticket['ESTADO'] ?? $ticket['estado'] ?? '';
            $demoras = $ticket['DEMORAS'] ?? $ticket['demoras'] ?? '';
            $datosFaltantes = $ticket['DATOS FALTANTES'] ?? $ticket['datos_faltantes'] ?? '';
            $fecha = $ticket['FECHA'] ?? $ticket['fecha'] ?? '';

            $stmt->bind_param("sssssss", $enlace, $descripcion, $cargaDeDatos, $estado, $demoras, $datosFaltantes, $fecha);
            if (!$stmt->execute()) {
                $success = false;
                break;
            }
        }

        if ($success) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Error al guardar tickets: " . $stmt->error]);
        }
        $stmt->close();

    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }

    exit;
}


// Si es GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_tickets') {
        header('Content-Type: application/json');
        $query = "SELECT * FROM tickets ORDER BY fecha DESC";
        $result = mysqli_query($conn, $query);

        $tickets = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $tickets[] = [
                "enlace" => $row["enlace"] ?? "",
                "descripcion" => $row["descripcion"] ?? "",
                "carga_datos" => $row["carga_datos"] ?? "",
                "estado" => $row["estado"] ?? "",
                "demoras" => $row["demoras"] ?? "",
                "datos_faltantes" => $row["datos_faltantes"] ?? "",
                "fecha" => $row["fecha"] ?? ""
            ];
        }
        echo json_encode($tickets);
        exit;
    } elseif ($action === 'navigate') {
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