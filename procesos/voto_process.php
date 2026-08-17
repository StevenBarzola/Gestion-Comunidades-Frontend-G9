<?php
require_once '../auth/session_check.php';
require_once '../config/api.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $asambleaId = intval($_POST['asamblea_id'] ?? 0);
    $opcionId   = intval($_POST['opcion_id'] ?? 0);

    // Con TokenAuthentication + perform_create en el backend, el 'usuario' NO se envía
    // desde aquí: Django lo asigna automáticamente a partir del token de la sesión.
    $votoData = [
        'asamblea' => $asambleaId,
        'opcion'   => $opcionId,
    ];

    $respuesta = callAPI('POST', 'votos/', $votoData);

    if (isset($respuesta['id'])) {
        $_SESSION['mensaje_exito'] = "Tu voto fue registrado correctamente.";
        header('Location: ../views/asambleas.php');
        exit();
    }

    // La API responde con 'non_field_errors' cuando se viola la restricción
    // unique_together (asamblea, usuario), es decir, cuando el usuario ya votó.
    if (isset($respuesta['non_field_errors'])) {
        $_SESSION['mensaje_error'] = "Ya emitiste tu voto en esta asamblea. Solo se permite un voto por usuario.";
    } else {
        $_SESSION['mensaje_error'] = "No se pudo registrar tu voto. Intenta nuevamente.";
    }

    header('Location: ../views/votar.php?id=' . $asambleaId);
    exit();
}

// Si alguien entra por GET directo, lo mandamos de vuelta al listado
header('Location: ../views/asambleas.php');
exit();