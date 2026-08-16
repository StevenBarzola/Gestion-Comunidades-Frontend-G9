<?php
    // 1. Proteger la ruta (Si no hay token, lo devuelve al Login)
    require_once '../auth/session_check.php';

    // 2. Cargar configuración de la API (para usar callAPI)
    require_once '../config/api.php';

    // 3. Consumir el endpoint GET de clubes
    $clubes = callAPI('GET', 'clubes/');
?>

<?php include '../includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: var(--primary-color);"><i class="fa-solid fa-users"></i> Directorio de Clubes</h2>
    
    <!-- Botón visual, deshabilitado (FALTA FORMULARIO DE CREACION) -->
    <button class="btn" style="background-color: var(--text-muted); cursor: not-allowed;" title="Formulario en construcción">
        <i class="fa-solid fa-plus"></i> Crear Nuevo Club
    </button>
</div>

<!-- Contenedor de Tarjetas en Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    
    <?php 
    // Arreglo valido de clubes verificación
    if (isset($clubes) && is_array($clubes) && count($clubes) > 0): 
    ?>
        <!-- Bucle para dibujar una tarjeta por cada club en la base de datos -->
        <?php foreach ($clubes as $club): ?>
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                
                <div>
                    <!-- Fila superior: Nombre y Siglas -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <h3 style="color: var(--primary-color); margin: 0;">
                            <?php echo htmlspecialchars($club['nombre'] ?? 'Sin nombre'); ?>
                        </h3>
                        <span style="background-color: var(--secondary-color); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                            <?php echo htmlspecialchars($club['siglas'] ?? 'S/N'); ?>
                        </span>
                    </div>
                    
                    <!-- Facultad -->
                    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 15px;">
                        <i class="fa-solid fa-building"></i> <?php echo htmlspecialchars($club['facultad'] ?? 'Facultad no especificada'); ?>
                    </p>
                    
                    <!-- Descripción -->
                    <p style="font-size: 14px; color: var(--text-dark);">
                        <?php echo htmlspecialchars($club['descripcion'] ?? 'Sin descripción disponible.'); ?>
                    </p>
                </div>
                
                <!-- Pie de la tarjeta -->
                <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; text-align: right;">
                    <a href="#" style="color: var(--primary-color); text-decoration: none; font-size: 14px; font-weight: bold;">
                        Ver detalles <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <!-- Si la base de datos no tiene clubes aún -->
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--text-muted);">
            <i class="fa-solid fa-folder-open fa-4x" style="margin-bottom: 15px; color: #ccc;"></i>
            <h3>Directorio Vacío</h3>
            <p>Aún no se han registrado agrupaciones estudiantiles en el sistema.</p>
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>