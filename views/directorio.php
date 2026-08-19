<?php
// 1. Proteger la ruta (Si no hay token, lo devuelve al Login)
require_once '../auth/session_check.php';

// 2. Cargar configuración de la API
require_once '../config/api.php';

// 3. Consumir el endpoint GET de clubes
$clubes = callAPI('GET', 'clubes/');
?>

<?php include '../includes/header.php'; ?>

<!-- Encabezado y Botón -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: var(--primary-color);"><i class="fa-solid fa-users"></i> Directorio de Clubes</h2>
    
    <!-- Botón activado que dispara el modal nativo -->
    <button class="btn" onclick="abrirModal()">
        <i class="fa-solid fa-plus"></i> Crear Nuevo Club
    </button>
</div>

<!-- Alertas de estado nativas -->
<?php if (isset($_SESSION['mensaje_club'])): ?>
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_SESSION['mensaje_club']); ?>
        <?php unset($_SESSION['mensaje_club']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_club'])): ?>
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_SESSION['error_club']); ?>
        <?php unset($_SESSION['error_club']); ?>
    </div>
<?php endif; ?>

<!-- Contenedor de Tarjetas en Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
    
    <?php if (isset($clubes) && is_array($clubes) && count($clubes) > 0): ?>
        <?php foreach ($clubes as $club): ?>
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <h3 style="color: var(--primary-color); margin: 0;">
                            <?= htmlspecialchars($club['nombre'] ?? 'Sin nombre'); ?>
                        </h3>
                        <span style="background-color: var(--secondary-color); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                            <?= htmlspecialchars($club['siglas'] ?? 'S/N'); ?>
                        </span>
                    </div>
                    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 15px;">
                        <i class="fa-solid fa-building"></i> <?= htmlspecialchars($club['facultad'] ?? 'Facultad no especificada'); ?>
                    </p>
                    <p style="font-size: 14px; color: var(--text-dark);">
                        <?= htmlspecialchars($club['descripcion'] ?? 'Sin descripción disponible.'); ?>
                    </p>
                </div>
                <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; text-align: right;">
                    <a href="detalle_club.php?id=<?= $club['id'] ?>" style="color: var(--primary-color); text-decoration: none; font-size: 14px; font-weight: bold;">
                        Ver detalles <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--text-muted);">
            <i class="fa-solid fa-folder-open fa-4x" style="margin-bottom: 15px; color: #ccc;"></i>
            <h3>Directorio Vacío</h3>
            <p>Aún no se han registrado agrupaciones estudiantiles en el sistema.</p>
        </div>
    <?php endif; ?>

</div>

<!-- Modal Nativo HTML/CSS (Registrar Nuevo Club) -->
<div id="modalNuevoClub" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
    <div class="card" style="background-color: #fff; padding: 30px; width: 90%; max-width: 500px; border-radius: 8px; position: relative;">
        
        <span onclick="cerrarModal()" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #999;">&times;</span>
        
        <h3 style="color: var(--primary-color); margin-top: 0; margin-bottom: 20px;">Registrar Nueva Agrupación</h3>
        
        <form action="../procesos/club_process.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label for="nombre" style="display: block; font-weight: bold; margin-bottom: 5px;">Nombre del Club</label>
                <input type="text" id="nombre" name="nombre" required placeholder="Ej: Club de Robótica Estudiantil" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="siglas" style="display: block; font-weight: bold; margin-bottom: 5px;">Siglas</label>
                <input type="text" id="siglas" name="siglas" required placeholder="Ej: CREE" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="facultad" style="display: block; font-weight: bold; margin-bottom: 5px;">Facultad (opcional)</label>
                <input type="text" id="facultad" name="facultad" placeholder="Ej: FIEC" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="descripcion" style="display: block; font-weight: bold; margin-bottom: 5px;">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Breve descripción de las actividades del club..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;"></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn" style="background-color: var(--text-muted);" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn">Guardar Club</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModal() {
        document.getElementById('modalNuevoClub').style.display = 'flex';
    }
    
    function cerrarModal() {
        document.getElementById('modalNuevoClub').style.display = 'none';
    }
    
    window.onclick = function(event) {
        var modal = document.getElementById('modalNuevoClub');
        if (event.target == modal) {
            cerrarModal();
        }
    }
</script>

<?php include '../includes/footer.php'; ?>