<?php
// Cierre de sesion: destruye la sesion PHP (token incluido) y regresa al login.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vaciar y destruir la sesion del servidor
$_SESSION = [];
session_unset();
session_destroy();

// Eliminar tambien la cookie de sesion del navegador
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

header('Location: ../index.php');
exit();
