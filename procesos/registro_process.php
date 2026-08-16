<?php
session_start();
require_once '../config/api.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $datos_registro = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password' => $_POST['password']
    ];

    $respuesta = callAPI('POST', 'registro/', $datos_registro);

    if (isset($respuesta['username'])) {
        // Guardamos el mensaje verde y redirigimos al Login
        $_SESSION['mensaje_exito'] = "Cuenta creada exitosamente. Ya puedes iniciar sesión.";
        header('Location: ../index.php');
        exit();
    } else {
        // Si falla (el usuario ya existe o faltan datos)
        $_SESSION['error_registro'] = "No se pudo crear la cuenta. Verifica los datos o intenta con otro usuario.";
        header('Location: ../views/registro.php');
        exit();
    }
}
?>