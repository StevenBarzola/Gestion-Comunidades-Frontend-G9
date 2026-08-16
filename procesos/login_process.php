<?php
session_start();
require_once '../config/api.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credenciales = [
        'username' => $_POST['username'],
        'password' => $_POST['password']
    ];

    // Llamamos al endpoint
    $respuesta = callAPI('POST', 'login/', $credenciales);

    if (isset($respuesta['token'])) {
        //Login exitoso: Guardamos el token y datos básicos en la sesión
        $_SESSION['api_token'] = $respuesta['token'];
        $_SESSION['username'] = $credenciales['username'];
        
        // Redirigir al directorio
        header('Location: ../views/directorio.php');
        exit();
    } else {
        // Login fallido (credenciales incorrectas)
        $_SESSION['error_login'] = "Usuario o contraseña incorrectos.";
        header('Location: ../index.php');
        exit();
    }
}
?>