<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Si no hay token en la sesión, el usuario no ha hecho login
    if (!isset($_SESSION['api_token'])) {
        // De vuelta a la página principal
        header('Location: ../index.php');
        exit();
    }
?>