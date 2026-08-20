<?php
require_once '../auth/session_check.php';
require_once '../config/api.php';

// Consumir el endpoint de asambleas
$asambleas = callAPI('GET', 'asambleas/');
$asambleas = is_array($asambleas) ? $asambleas : [];

// NUEVO: Consumir clubes para el formulario de creación
$clubes = callAPI('GET', 'clubes/');
$clubes = is_array($clubes) ? $clubes : [];
?>

<?php include '../includes/header.php'; ?>

<!-- Encabezado y Botón -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: var(--primary-color);"><i class="fa-solid fa-check-to-slot"></i> Asambleas y Votaciones</h2>
    
    <!-- Botón que dispara el modal -->
    <button class="btn" onclick="abrirModalAsamblea()">
        <i class="fa-solid fa-plus"></i> Nueva Asamblea
    </button>
</div>

<!-- Alertas nativas -->
<?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_SESSION['mensaje_exito']); ?>
        <?php unset($_SESSION['mensaje_exito']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['mensaje_error'])): ?>
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_SESSION['mensaje_error']); ?>
        <?php unset($_SESSION['mensaje_error']); ?>
    </div>
<?php endif; ?>

<!-- Listado de asambleas en tarjetas -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">

    <?php if (count($asambleas) > 0): ?>
        <?php foreach ($asambleas as $asamblea): ?>
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <h3 style="color: var(--primary-color); margin: 0;">
                            <?= htmlspecialchars($asamblea['titulo'] ?? 'Sin título'); ?>
                        </h3>

                        <?php if (!empty($asamblea['activa'])): ?>
                            <span style="background-color: var(--success); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Activa</span>
                        <?php else: ?>
                            <span style="background-color: var(--text-muted); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Cerrada</span>
                        <?php endif; ?>
                    </div>

                    <p style="color: var(--text-muted); font-size: 14px;">
                        <i class="fa-solid fa-calendar-days"></i>
                        Cierra: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($asamblea['fecha_cierre'] ?? 'now'))); ?>
                    </p>
                </div>

                <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; text-align: right;">
                    <?php if (!empty($asamblea['activa'])): ?>
                        <a href="votar.php?id=<?= (int) $asamblea['id']; ?>" class="btn">
                            <i class="fa-solid fa-square-poll-vertical"></i> Votar
                        </a>
                    <?php else: ?>
                        <a href="votar.php?id=<?= (int) $asamblea['id']; ?>" class="btn" style="background-color: var(--text-muted);">
                            <i class="fa-solid fa-chart-simple"></i> Ver resultados
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 50px; color: var(--text-muted);">
            <i class="fa-solid fa-folder-open fa-4x" style="margin-bottom: 15px; color: #ccc;"></i>
            <h3>No hay asambleas registradas</h3>
            <p>Cuando la directiva de un club abra una asamblea, aparecerá aquí.</p>
        </div>
    <?php endif; ?>
</div>

<div id="modalAsamblea" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; overflow-y: auto;">
    <div class="card" style="background-color: #fff; padding: 30px; width: 90%; max-width: 600px; border-radius: 8px; position: relative; max-height: 90vh; overflow-y: auto;">
        
        <span onclick="cerrarModalAsamblea()" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #999;">&times;</span>
        
        <h3 style="color: var(--primary-color); margin-top: 0; margin-bottom: 20px;"><i class="fa-solid fa-gavel"></i> Aperturar Asamblea</h3>
        
        <form action="../procesos/asamblea_process.php" method="POST">
            <!-- DATOS DE LA ASAMBLEA -->
            <div style="margin-bottom: 15px;">
                <label for="titulo" style="display: block; font-weight: bold; margin-bottom: 5px;">Título de la Votación</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Ej: Elección Directiva 2026" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label for="club" style="display: block; font-weight: bold; margin-bottom: 5px;">Club que organiza</label>
                    <select id="club" name="club" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">-- Selecciona --</option>
                        <?php foreach ($clubes as $c): ?>
                            <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['siglas']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label for="fecha_cierre" style="display: block; font-weight: bold; margin-bottom: 5px;">Fecha de Cierre</label>
                    <input type="datetime-local" id="fecha_cierre" name="fecha_cierre" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
            </div>

            <hr style="border: 0; border-top: 2px solid #eee; margin: 20px 0;">

            <!-- DATOS DE LAS OPCIONES DE VOTO -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="color: var(--primary-color); margin: 0;"><i class="fa-solid fa-list-ul"></i> Opciones de Votación</h4>
                <button type="button" class="btn" style="background-color: var(--secondary-color); padding: 5px 10px; font-size: 13px;" onclick="agregarOpcion()">
                    <i class="fa-solid fa-plus"></i> Añadir opción
                </button>
            </div>

            <div id="contenedor-opciones">
                <!-- Opción 1 (Obligatoria por defecto) -->
                <div class="opcion-bloque" style="background-color: var(--background-light); padding: 15px; border-radius: 4px; margin-bottom: 10px; border: 1px solid #e0e0e0;">
                    <input type="text" name="opciones_nombre[]" required placeholder="Nombre de lista, candidato o ítem" style="width: 100%; padding: 8px; margin-bottom: 8px; border: 1px solid #ccc; border-radius: 4px; font-weight: bold;">
                    <textarea name="opciones_desc[]" rows="2" placeholder="Descripción breve (opcional)" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;"></textarea>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px;">
                <button type="button" class="btn" style="background-color: var(--text-muted);" onclick="cerrarModalAsamblea()">Cancelar</button>
                <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Crear y Guardar Todo</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalAsamblea() { document.getElementById('modalAsamblea').style.display = 'flex'; }
    function cerrarModalAsamblea() { document.getElementById('modalAsamblea').style.display = 'none'; }
    
    // Función para clonar y agregar más bloques de opciones
    function agregarOpcion() {
        const contenedor = document.getElementById('contenedor-opciones');
        const nuevoBloque = document.createElement('div');
        nuevoBloque.className = 'opcion-bloque';
        nuevoBloque.style = 'background-color: var(--background-light); padding: 15px; border-radius: 4px; margin-bottom: 10px; border: 1px solid #e0e0e0; position: relative;';
        
        // Se añade un botón de eliminar (X) en la esquina de cada bloque nuevo
        nuevoBloque.innerHTML = `
            <span onclick="this.parentElement.remove()" style="position: absolute; right: 10px; top: 10px; color: #dc3545; cursor: pointer; font-weight: bold;" title="Eliminar opción">&times;</span>
            <input type="text" name="opciones_nombre[]" required placeholder="Nombre de lista, candidato o ítem" style="width: 100%; padding: 8px; margin-bottom: 8px; border: 1px solid #ccc; border-radius: 4px; font-weight: bold;">
            <textarea name="opciones_desc[]" rows="2" placeholder="Descripción breve (opcional)" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;"></textarea>
        `;
        contenedor.appendChild(nuevoBloque);
    }
</script>

<?php include '../includes/footer.php'; ?>