<?php
    // 1. Inicializar la sesión
    session_start();

    // 2. Liberar todas las variables de sesión actuales (incluyendo el token)
    session_unset();

    // 3. Destruir la sesión por completo en el servidor
    session_destroy();

    // 4. Redirigir mediante cabecera a la pantalla de Login (index.php)
    header('Location: ../index.php');
    exit();
?>