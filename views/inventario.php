<?php
// 1. Proteger la ruta y cargar configuración
require_once '../auth/session_check.php';
require_once '../config/api.php';
require_once '../includes/permisos.php';

// Capturar los filtros desde los parámetros GET de la URL
$filtroEstado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$filtroClub   = isset($_GET['club']) ? trim($_GET['club']) : '';

// Construir parámetros para la API
$paramsAPI = [];
if (!empty($filtroEstado)) {
    $paramsAPI['estado'] = $filtroEstado;
}
if (!empty($filtroClub)) {
    $paramsAPI['club'] = $filtroClub;
}

// Consultar la API pasando los filtros directamente en la URL
$inventario = callAPI('GET', 'inventario/', $paramsAPI);
$clubes     = callAPI('GET', 'clubes/');

// Asegurar que las variables sean arrays
$inventario = is_array($inventario) ? $inventario : [];
$clubes     = is_array($clubes) ? $clubes : [];

// El inventario está aislado por club: solo se ve el de las agrupaciones a las
// que se pertenece. Por eso los selectores solo ofrecen esos clubes, evitando
// registrar un ítem en un club ajeno que luego no sería visible.
$perfil = obtenerPerfil();
if (empty($perfil['is_staff'])) {
    $misClubesIds = array_map(fn($m) => $m['club_id'], $perfil['membresias']);
    $clubes = array_values(array_filter($clubes, fn($c) => in_array($c['id'], $misClubesIds)));
}

// Mapeo rápido de ID de club a nombre
$mapaClubes = [];
foreach ($clubes as $club) {
    if (isset($club['id'], $club['nombre'])) {
        $mapaClubes[$club['id']] = $club['nombre'] . ' [' . $club['siglas'] . ']';
    }
}
?>

<?php include '../includes/header.php'; ?>

<!-- Encabezado idéntico al de directorio.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: var(--primary-color);"><i class="fa-solid fa-boxes-stacked"></i> Inventario de Bienes</h2>
    
    <!-- Botón con las clases de tu style.css que activa el modal nativo -->
    <button class="btn" onclick="abrirModal()">
        <i class="fa-solid fa-plus"></i> Nuevo Ítem
    </button>
</div>

<!-- Alertas de estado con estilos nativos -->
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

<!-- Panel de Filtros -->
<div class="card" style="margin-bottom: 25px; padding: 20px;">
    <form method="GET" action="inventario.php" style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end;">
        <div style="flex: 1; min-width: 250px;">
            <label for="filtroClub" style="display: block; font-weight: bold; margin-bottom: 8px;">Filtrar por Club:</label>
            <select name="club" id="filtroClub" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;" onchange="this.form.submit()">
                <option value="">-- Todos los Clubes --</option>
                <?php foreach ($clubes as $c): ?>
                    <option value="<?= $c['id']; ?>" <?= $filtroClub == $c['id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($c['nombre']); ?> (<?= htmlspecialchars($c['siglas']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="flex: 1; min-width: 250px;">
            <label for="filtroEstado" style="display: block; font-weight: bold; margin-bottom: 8px;">Filtrar por Disponibilidad:</label>
            <select name="estado" id="filtroEstado" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;" onchange="this.form.submit()">
                <option value="" <?= $filtroEstado === '' ? 'selected' : ''; ?>>-- Todos los Estados --</option>
                <option value="Disponible" <?= $filtroEstado === 'Disponible' ? 'selected' : ''; ?>>Disponible</option>
                <option value="Prestado" <?= $filtroEstado === 'Prestado' ? 'selected' : ''; ?>>Prestado</option>
            </select>
        </div>

        <div>
            <?php if (!empty($filtroEstado) || !empty($filtroClub)): ?>
                <a href="inventario.php" class="btn" style="background-color: var(--text-muted); color: white; text-decoration: none; display: inline-block;">Limpiar Filtros</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Tabla de Inventario -->
<div class="card" style="overflow-x: auto; padding: 20px;">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 15px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--primary-color);">
                <th style="padding: 12px 8px; color: var(--primary-color);">Código</th>
                <th style="padding: 12px 8px; color: var(--primary-color);">Nombre / Descripción</th>
                <th style="padding: 12px 8px; color: var(--primary-color);">Categoría</th>
                <th style="padding: 12px 8px; color: var(--primary-color);">Club Perteneciente</th>
                <th style="padding: 12px 8px; color: var(--primary-color);">Estado</th>
                <th style="padding: 12px 8px; color: var(--primary-color); text-align: center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($inventario)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                        No se encontraron ítems con los criterios de búsqueda seleccionados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($inventario as $item): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px 8px;"><strong><?= htmlspecialchars($item['codigo']); ?></strong></td>
                        <td style="padding: 12px 8px;"><?= htmlspecialchars($item['nombre']); ?></td>
                        <td style="padding: 12px 8px;"><?= htmlspecialchars($item['categoria']); ?></td>
                        <td style="padding: 12px 8px;"><?= htmlspecialchars($mapaClubes[$item['club']] ?? 'Club #' . $item['club']); ?></td>
                        <td style="padding: 12px 8px;">
                            <?php if ($item['estado'] === 'Disponible'): ?>
                                <span style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Disponible</span>
                            <?php else: ?>
                                <span style="background-color: #ffc107; color: #333; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Prestado</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px 8px; text-align: center;">
                            <form action="../procesos/inventario_process.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este ítem?');" style="margin: 0;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="item_id" value="<?= $item['id']; ?>">
                                <button type="submit" style="background-color: transparent; border: 1px solid #dc3545; color: #dc3545; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 13px;">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Nativo HTML/CSS (Registrar Nuevo Ítem) -->
<div id="modalNuevoItem" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
    <div class="card" style="background-color: #fff; padding: 30px; width: 90%; max-width: 500px; border-radius: 8px; position: relative;">
        
        <!-- Botón de cerrar -->
        <span onclick="cerrarModal()" style="position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #999;">&times;</span>
        
        <h3 style="color: var(--primary-color); margin-top: 0; margin-bottom: 20px;">Registrar Ítem de Inventario</h3>
        
        <form action="../procesos/inventario_process.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label for="codigo" style="display: block; font-weight: bold; margin-bottom: 5px;">Código del Bien</label>
                <input type="text" id="codigo" name="codigo" required placeholder="Ej. INV-2026-001" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="nombre" style="display: block; font-weight: bold; margin-bottom: 5px;">Nombre o Descripción</label>
                <input type="text" id="nombre" name="nombre" required placeholder="Ej. Proyector Epson HD" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="categoria" style="display: block; font-weight: bold; margin-bottom: 5px;">Categoría</label>
                <input type="text" id="categoria" name="categoria" required placeholder="Ej. Audiovisual, Cómputo" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="club" style="display: block; font-weight: bold; margin-bottom: 5px;">Club Asignado</label>
                <select id="club" name="club" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">-- Selecciona un club --</option>
                    <?php foreach ($clubes as $c): ?>
                        <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['nombre']); ?> (<?= htmlspecialchars($c['siglas']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom: 20px;">
                <label for="estado_item" style="display: block; font-weight: bold; margin-bottom: 5px;">Estado Inicial</label>
                <select id="estado_item" name="estado" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="Disponible">Disponible</option>
                    <option value="Prestado">Prestado</option>
                </select>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn" style="background-color: var(--text-muted);" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn">Guardar Ítem</button>
            </div>
        </form>
    </div>
</div>

<!-- Lógica JS simple para el Modal -->
<script>
    function abrirModal() {
        document.getElementById('modalNuevoItem').style.display = 'flex';
    }
    
    function cerrarModal() {
        document.getElementById('modalNuevoItem').style.display = 'none';
    }
    
    // Cerrar modal al hacer clic fuera del cuadro
    window.onclick = function(event) {
        var modal = document.getElementById('modalNuevoItem');
        if (event.target == modal) {
            cerrarModal();
        }
    }
</script>

<?php include '../includes/footer.php'; ?>