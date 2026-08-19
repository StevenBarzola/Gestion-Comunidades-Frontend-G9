<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clubes ESPOL</title>
    <!-- Importar la hoja de estilos base -->
    <link rel="stylesheet" href="../assets/style.css">
    
    <!-- LIBRERÍA DE ICONOS: FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav style="background: var(--primary-color); color: white; padding: 15px 0;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0;"><i class="fa-solid fa-layer-group"></i> Plataforma de Clubes</h2>
            <div style="display: flex; align-items: center;">
                <a href="directorio.php" style="color: white; text-decoration: none; margin-right: 15px;"><i class="fa-solid fa-address-book"></i> Directorio</a>
                <a href="inventario.php" style="color: white; text-decoration: none; margin-right: 15px;"><i class="fa-solid fa-boxes-stacked"></i> Inventario</a>
                <a href="asambleas.php" style="color: white; text-decoration: none; margin-right: 25px;"><i class="fa-solid fa-check-to-slot"></i> Asambleas</a>
                
                <!-- LOGOUT -->
                <a href="../auth/logout.php" style="background-color: #dc3545; color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; font-weight: bold; font-size: 14px; display: inline-block;">
                    <i class="fa-solid fa-right-from-bracket"></i> Salir
                </a>
            </div>
        </div>
    </nav>
    <main class="container">