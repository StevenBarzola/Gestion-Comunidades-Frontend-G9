<?php
require_once '../auth/session_check.php';
require_once '../config/api.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ACCIÓN: ELIMINAR ÍTEM
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $itemId = intval($_POST['item_id']);
        
        // Llamada DELETE a la API de Django (e.g. /api/inventario/5/)
        $respuesta = callAPI('DELETE', 'inventario/' . $itemId . '/');

        $_SESSION['mensaje_exito'] = "Ítem eliminado correctamente del inventario.";
        header('Location: ../views/inventario.php');
        exit();
    }

    // ACCIÓN: CREAR ÍTEM (Código existente)
    $itemData = [
        'codigo'    => trim($_POST['codigo']),
        'nombre'    => trim($_POST['nombre']),
        'categoria' => trim($_POST['categoria']),
        'estado'    => $_POST['estado'] ?? 'Disponible',
        'club'      => intval($_POST['club'])
    ];

    $respuesta = callAPI('POST', 'inventario/', $itemData);

    if (isset($respuesta['id'])) {
        $_SESSION['mensaje_exito'] = "Ítem registrado correctamente en el inventario.";
    } else {
        $_SESSION['mensaje_error'] = "Error al registrar el ítem. Verifica que el código no esté duplicado.";
    }

    header('Location: ../views/inventario.php');
    exit();
}
?>