<?php
// auth.php - Solo lógica pura, sin echos ni exits (salvo debug temporal)
require_once 'config.php';

session_start(); // Iniciar la sesión aquí para poder usar $_SESSION

function login($email, $password)
{
    global $conn;
    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    if ($result->num_rows > 0) {
        $_SESSION['logueado'] = true;
        $_SESSION['user_email'] = $email;
        return true;
    }

    return false;
}

function logout()
{
    session_destroy();
    return true;
}

function is_logged_in()
{
    return isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
}


function register($email, $password)
{

    global $conn;
    $query = "INSERT INTO users (email, password) VALUES ('$email', '$password')";
    $result = mysqli_query($conn, $query);
    return $result;
}

function getGoogleLoginUrl()
{
    global $google_client_id, $google_redirect_uri;
    $params = [
        'response_type' => 'code',
        'client_id' => $google_client_id,
        'redirect_uri' => $google_redirect_uri,
        'scope' => 'email profile',
        'access_type' => 'online',
        'prompt' => 'consent' // Forces consent screen to ensure proper flow for testing
    ];
    return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
}

function getGoogleAccessToken($code)
{
    global $google_client_id, $google_client_secret, $google_redirect_uri;

    $url = 'https://oauth2.googleapis.com/token';
    $params = [
        'code' => $code,
        'client_id' => $google_client_id,
        'client_secret' => $google_client_secret,
        'redirect_uri' => $google_redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function getGoogleUserInfo($accessToken)
{
    $url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $accessToken;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function loginWithGoogle($email)
{
    global $conn;

    // Check if user exists
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result->num_rows > 0) {
        // User exists, login
        $_SESSION['logueado'] = true;
        $_SESSION['user_email'] = $email;
        return true;
    } else {
        // User doesn't exist, register then login
        // Use a random password since they use Google
        $password = bin2hex(random_bytes(10));
        register($email, $password);

        $_SESSION['logueado'] = true;
        $_SESSION['user_email'] = $email;
        return true;
    }
}
?>