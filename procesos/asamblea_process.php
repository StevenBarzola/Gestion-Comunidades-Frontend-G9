<?php
// ==========================================
// PARTE DE ISAAC: administración de Asambleas
// Maneja las acciones de la directiva sobre una asamblea:
// crear, agregar opciones, eliminar opciones, abrir y cerrar la votación.
// ==========================================

require_once '../auth/session_check.php';
require_once '../config/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/asambleas.php');
    exit();
}

$accion = $_POST['accion'] ?? '';

/** Extrae un mensaje legible del cuerpo de error devuelto por la API. */
function mensajeDeError($respuesta, $porDefecto)
{
    if (!is_array($respuesta)) {
        return $porDefecto;
    }
    if (isset($respuesta['detail'])) {
        return $respuesta['detail'];
    }
    foreach ($respuesta as $campo => $errores) {
        if (is_array($errores) && !empty($errores)) {
            return is_string($errores[0]) ? $errores[0] : $porDefecto;
        }
        if (is_string($errores)) {
            return $errores;
        }
    }
    return $porDefecto;
}

switch ($accion) {

    // ---------------------------------------------------------------
    case 'crear_asamblea':
        $titulo = trim($_POST['titulo'] ?? '');
        $clubId = intval($_POST['club'] ?? 0);
        $fecha  = trim($_POST['fecha_cierre'] ?? '');

        if ($titulo === '' || !$clubId || $fecha === '') {
            $_SESSION['mensaje_error'] = "Completa el título, el club y la fecha de cierre.";
            header('Location: ../views/asambleas.php');
            exit();
        }

        // El input datetime-local entrega "2026-08-25T18:00"; la API espera ISO.
        $fechaIso = str_replace(' ', 'T', $fecha);
        if (strlen($fechaIso) === 16) {
            $fechaIso .= ':00';
        }

        $respuesta = callAPI('POST', 'asambleas/', [
            'titulo'       => $titulo,
            'club'         => $clubId,
            'fecha_cierre' => $fechaIso,
            'activa'       => false,
        ]);

        if (isset($respuesta['id'])) {
            $_SESSION['mensaje_exito'] = "Asamblea creada en borrador. Agrega las opciones de voto para poder abrirla.";
            header('Location: ../views/gestionar_asamblea.php?id=' . intval($respuesta['id']));
        } else {
            $_SESSION['mensaje_error'] = mensajeDeError($respuesta, "No se pudo crear la asamblea.");
            header('Location: ../views/asambleas.php');
        }
        exit();

    // ---------------------------------------------------------------
    case 'crear_opcion':
        $asambleaId = intval($_POST['asamblea_id'] ?? 0);
        $nombre     = trim($_POST['nombre_lista'] ?? '');
        $desc       = trim($_POST['descripcion'] ?? '');

        if (!$asambleaId || $nombre === '') {
            $_SESSION['mensaje_error'] = "El nombre de la opción es obligatorio.";
            header('Location: ../views/gestionar_asamblea.php?id=' . $asambleaId);
            exit();
        }

        $respuesta = callAPI('POST', 'opciones-voto/', [
            'asamblea'    => $asambleaId,
            'nombre_lista' => $nombre,
            'descripcion' => $desc,
        ]);

        if (isset($respuesta['id'])) {
            $_SESSION['mensaje_exito'] = "Opción agregada a la papeleta.";
        } else {
            $_SESSION['mensaje_error'] = mensajeDeError($respuesta, "No se pudo agregar la opción.");
        }
        header('Location: ../views/gestionar_asamblea.php?id=' . $asambleaId);
        exit();

    // ---------------------------------------------------------------
    case 'eliminar_opcion':
        $asambleaId = intval($_POST['asamblea_id'] ?? 0);
        $opcionId   = intval($_POST['opcion_id'] ?? 0);

        $respuesta = callAPI('DELETE', 'opciones-voto/' . $opcionId . '/');

        // Un DELETE exitoso devuelve 204 sin cuerpo.
        if (!is_array($respuesta) || empty($respuesta)) {
            $_SESSION['mensaje_exito'] = "Opción eliminada de la papeleta.";
        } else {
            $_SESSION['mensaje_error'] = mensajeDeError($respuesta, "No se pudo eliminar la opción.");
        }
        header('Location: ../views/gestionar_asamblea.php?id=' . $asambleaId);
        exit();

    // ---------------------------------------------------------------
    case 'activar':
        $asambleaId = intval($_POST['asamblea_id'] ?? 0);
        $respuesta = callAPI('POST', 'asambleas/' . $asambleaId . '/activar/', []);

        if (isset($respuesta['activa']) && $respuesta['activa'] === true) {
            $_SESSION['mensaje_exito'] = "La votación está abierta. Los miembros ya pueden emitir su voto.";
        } else {
            $_SESSION['mensaje_error'] = mensajeDeError($respuesta, "No se pudo abrir la votación.");
        }
        header('Location: ../views/gestionar_asamblea.php?id=' . $asambleaId);
        exit();

    // ---------------------------------------------------------------
    case 'cerrar':
        $asambleaId = intval($_POST['asamblea_id'] ?? 0);
        $respuesta = callAPI('POST', 'asambleas/' . $asambleaId . '/cerrar/', []);

        if (isset($respuesta['activa']) && $respuesta['activa'] === false) {
            $_SESSION['mensaje_exito'] = "Votación cerrada. Los resultados ya están disponibles.";
        } else {
            $_SESSION['mensaje_error'] = mensajeDeError($respuesta, "No se pudo cerrar la votación.");
        }
        header('Location: ../views/gestionar_asamblea.php?id=' . $asambleaId);
        exit();

    // ---------------------------------------------------------------
    default:
        header('Location: ../views/asambleas.php');
        exit();
}
