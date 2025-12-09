<?php
// auth.php - Solo lógica pura, sin echos ni exits (salvo debug temporal)

session_start(); // Iniciar la sesión aquí para poder usar $_SESSION

function login($email, $password)
{
    // Credenciales hardcodeadas por ahora
    $valid_user = "admin@admin.com";
    $valid_pass = "admin123";

    if ($email === $valid_user && $password === $valid_pass) {
        $_SESSION['logueado'] = true;
        $_SESSION['user_email'] = $email;
        return true;
    }

    return false;
}

function logout()
{
    session_destroy();
}

function is_logged_in()
{
    return isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
}
?>