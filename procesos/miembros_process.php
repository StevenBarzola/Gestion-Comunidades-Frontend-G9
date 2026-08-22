<?php
// ==========================================
// Administración de miembros de un club (solo directiva)
// Aprobar aspirantes, cambiar roles internos y dar de baja.
// ==========================================

require_once '../auth/session_check.php';
require_once '../config/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/directorio.php');
    exit();
}

$accion      = $_POST['accion'] ?? '';
$clubId      = intval($_POST['club_id'] ?? 0);
$membresiaId = intval($_POST['membresia_id'] ?? 0);

/** Extrae un mensaje legible del cuerpo de error devuelto por la API. */
function mensajeError($respuesta, $porDefecto)
{
    if (!is_array($respuesta)) {
        return $porDefecto;
    }
    if (isset($respuesta['detail'])) {
        return $respuesta['detail'];
    }
    foreach ($respuesta as $errores) {
        if (is_array($errores) && !empty($errores) && is_string($errores[0])) {
            return $errores[0];
        }
        if (is_string($errores)) {
            return $errores;
        }
    }
    return $porDefecto;
}

$volver = '../views/gestionar_miembros.php?club=' . $clubId;

switch ($accion) {

    // ---------------------------------------------------------------
    case 'cambiar_rol':
        $nuevoRol = $_POST['rol'] ?? '';
        $rolesValidos = ['presidente', 'vicepresidente', 'secretario',
                         'coor_eventos', 'coor_rsocial', 'miembro', 'aspirante'];

        if (!$membresiaId || !in_array($nuevoRol, $rolesValidos, true)) {
            $_SESSION['mensaje_error'] = "Rol no válido.";
            header('Location: ' . $volver);
            exit();
        }

        $respuesta = callAPI('PUT', 'membresias/' . $membresiaId . '/', [
            'club' => $clubId,
            'rol'  => $nuevoRol,
        ]);

        if (isset($respuesta['id'])) {
            $_SESSION['mensaje_exito'] = "Rol actualizado correctamente.";
        } else {
            $_SESSION['mensaje_error'] = mensajeError($respuesta, "No se pudo actualizar el rol.");
        }
        header('Location: ' . $volver);
        exit();

    // ---------------------------------------------------------------
    case 'aprobar':
        // Atajo del flujo de aprobación: aspirante -> miembro
        $respuesta = callAPI('PUT', 'membresias/' . $membresiaId . '/', [
            'club' => $clubId,
            'rol'  => 'miembro',
        ]);

        if (isset($respuesta['id'])) {
            $_SESSION['mensaje_exito'] = "Aspirante aprobado. Ya puede votar en las asambleas del club.";
        } else {
            $_SESSION['mensaje_error'] = mensajeError($respuesta, "No se pudo aprobar al aspirante.");
        }
        header('Location: ' . $volver);
        exit();

    // ---------------------------------------------------------------
    case 'expulsar':
        $respuesta = callAPI('DELETE', 'membresias/' . $membresiaId . '/');

        // Un DELETE exitoso devuelve 204 sin cuerpo.
        if (!is_array($respuesta) || empty($respuesta)) {
            $_SESSION['mensaje_exito'] = "El integrante fue dado de baja del club.";
        } else {
            $_SESSION['mensaje_error'] = mensajeError($respuesta, "No se pudo dar de baja al integrante.");
        }
        header('Location: ' . $volver);
        exit();

    // ---------------------------------------------------------------
    default:
        header('Location: ../views/directorio.php');
        exit();
}
