<?php
session_start();

// Si el usuario ya tiene un token guardado (ya inició sesión),
// lo redirigimos automáticamente al directorio para que no vuelva a ver el login.
if (isset($_SESSION['api_token'])) {
    header('Location: views/directorio.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Clubes ESPOL</title>
    <!-- Importamos los estilos base -->
    <link rel="stylesheet" href="assets/style.css">
    <!-- Para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Estilos específicos para la pantalla de Login */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: var(--background-light);
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: var(--border-radius);
            margin-top: 8px;
            font-size: 16px;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .alert-error {
            background-color: #f8d7da;
            color: var(--danger);
            padding: 10px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

    <div class="card login-card">
        <i class="fa-solid fa-users-rectangle fa-4x" style="color: var(--primary-color); margin-bottom: 15px;"></i>
        
        <h2 style="color: var(--primary-color); margin-bottom: 25px;">Plataforma de Clubes</h2>

        <?php
        if (isset($_SESSION['error_login'])) {
            echo '<div class="alert-error"><i class="fa-solid fa-triangle-exclamation"></i> ' . $_SESSION['error_login'] . '</div>';
            unset($_SESSION['error_login']);
        }
        ?>

        <?php
        if (isset($_SESSION['mensaje_exito'])) {
            echo '<div style="background-color: #d4edda; color: var(--success); padding: 10px; border-radius: var(--border-radius); margin-bottom: 20px; font-size: 14px; border: 1px solid #c3e6cb;"><i class="fa-solid fa-circle-check"></i> ' . $_SESSION['mensaje_exito'] . '</div>';
            unset($_SESSION['mensaje_exito']);
        }
        ?>

        <form action="procesos/login_process.php" method="POST">
            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user"></i> Usuario:</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Ej: sbarzola" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%; font-size: 16px; padding: 12px;">
                Ingresar <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>
        <div style="margin-top: 20px;">
            <a href="views/registro.php" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">¿No tienes cuenta? <strong style="color: var(--primary-color);">Regístrate aquí</strong></a>
        </div>
    </div>

</body>
</html>