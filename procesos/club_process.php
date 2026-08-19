<?php
session_start();
require_once '../config/api.php';
require_once '../auth/session_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $datos_club = [
        'nombre' => $_POST['nombre'],
        'siglas' => $_POST['siglas'],
        'facultad' => $_POST['facultad'],
        'descripcion' => $_POST['descripcion']
    ];

    $respuesta = callAPI('POST', 'clubes/', $datos_club);

    if (isset($respuesta['id'])) {
        $_SESSION['mensaje_club'] = "¡El club " . htmlspecialchars($datos_club['siglas']) . " fue registrado exitosamente!";
        header('Location: ../views/directorio.php');
        exit();
    } else {
        $_SESSION['error_club'] = "Hubo un problema al registrar el club. Verifica los datos.";
        header('Location: ../views/directorio.php');
        exit();
    }
} else {
    header('Location: ../views/directorio.php');
    exit();
}
?>