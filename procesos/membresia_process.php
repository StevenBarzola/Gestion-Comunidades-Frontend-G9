<?php
session_start();
require_once '../config/api.php';
require_once '../auth/session_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['club_id'])) {
    
    $id_club = $_POST['club_id'];
    
    // Solo enviamos el club_id. 
    // Django tomará el usuario de tu token y asignará 'aspirante' por defecto.
    $datos_membresia = [
        'club' => $id_club
    ];

    // Asumiendo que tu endpoint en Django se llama 'membresias/'
    $respuesta = callAPI('POST', 'membresias/', $datos_membresia);

    if (isset($respuesta['id'])) {
        $_SESSION['mensaje_membresia'] = "¡Solicitud enviada exitosamente! Ahora eres aspirante.";
    } else {
        // En caso de que Django te rebote por la regla unique_together (ya estás en el club)
        $_SESSION['mensaje_membresia'] = "Ya tienes un rol registrado en esta agrupación.";
    }
    
    // Retornar a la vista de detalles del mismo club
    header('Location: ../views/detalle_club.php?id=' . $id_club);
    exit();

} else {
    header('Location: ../views/directorio.php');
    exit();
}
?>