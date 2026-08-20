<?php
session_start();
require_once '../config/api.php';
require_once '../auth/session_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Crear la asamblea base
    $datos_asamblea = [
        'titulo' => $_POST['titulo'],
        'club' => $_POST['club'],
        'fecha_cierre' => $_POST['fecha_cierre'],
        'activa' => true
    ];

    $respuesta = callAPI('POST', 'asambleas/', $datos_asamblea);

    // Si la asamblea se guardó correctamente, procesamos las opciones
    if (isset($respuesta['id'])) {
        $nuevo_asamblea_id = $respuesta['id'];
        
        // 2. Extraer las opciones enviadas (vienen como arreglos desde HTML)
        if (isset($_POST['opciones_nombre']) && is_array($_POST['opciones_nombre'])) {
            $nombres = $_POST['opciones_nombre'];
            $descripciones = $_POST['opciones_desc'] ?? []; // Por si enviaron algunas vacías

            // Iterar sobre cada bloque ingresado en el formulario
            for ($i = 0; $i < count($nombres); $i++) {
                $nombre_actual = trim($nombres[$i]);
                $desc_actual = isset($descripciones[$i]) ? trim($descripciones[$i]) : '';

                // Si el nombre no está vacío, enviamos el POST a OpcionVoto
                if (!empty($nombre_actual)) {
                    $datos_opcion = [
                        'asamblea' => $nuevo_asamblea_id,
                        'nombre_lista' => $nombre_actual,
                        'descripcion' => $desc_actual
                    ];
                    // Asumiendo que el endpoint de Django es 'opciones-voto/' 
                    callAPI('POST', 'opciones-voto/', $datos_opcion);
                }
            }
        }
        
        $_SESSION['mensaje_exito'] = "La asamblea '{$datos_asamblea['titulo']}' y sus opciones de votación han sido registradas exitosamente.";
    } else {
        $_SESSION['mensaje_error'] = "Hubo un error al crear la asamblea. Verifica los datos.";
    }
    
    header('Location: ../views/asambleas.php');
    exit();

} else {
    header('Location: ../views/asambleas.php');
    exit();
}
?>