<?php
require_once '../auth/session_check.php';
require_once '../config/api.php';
require_once '../includes/permisos.php';

// Consumir el endpoint de asambleas del backend
$asambleas = callAPI('GET', 'asambleas/');
$asambleas = is_array($asambleas) ? $asambleas : [];

// GET /api/votos/ devuelve únicamente los votos del usuario autenticado,
// así que sirve para marcar en qué asambleas ya participó sin exponer a terceros.
$misVotos = callAPI('GET', 'votos/');
$misVotos = is_array($misVotos) ? $misVotos : [];

$asambleasVotadas = [];
foreach ($misVotos as $v) {
    if (isset($v['asamblea'])) {
        $asambleasVotadas[intval($v['asamblea'])] = true;
    }
}

// Perfil y clubes que el usuario administra (para mostrar u ocultar la
// creación de asambleas y el botón de gestión).
$perfil = obtenerPerfil();
$clubesAdmin = clubesQueAdministra($perfil);
$esDirectivo = count($clubesAdmin) > 0;
?>

<?php include '../includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: var(--primary-color);"><i class="fa-solid fa-check-to-slot"></i> Asambleas y Votaciones</h2>

    <?php if ($esDirectivo): ?>
        <button type="button" class="btn" onclick="document.getElementById('modalAsamblea').style.display='flex'">
            <i class="fa-solid fa-plus"></i> Nueva Asamblea
        </button>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="card" style="border-left: 4px solid var(--success); color: var(--success);">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_SESSION['mensaje_exito']); ?>
    </div>
    <?php unset($_SESSION['mensaje_exito']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="card" style="border-left: 4px solid var(--danger); color: var(--danger);">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_SESSION['mensaje_error']); ?>
    </div>
    <?php unset($_SESSION['mensaje_error']); ?>
<?php endif; ?>

<!-- Listado de asambleas en tarjetas -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">

    <?php if (count($asambleas) > 0): ?>
        <?php foreach ($asambleas as $asamblea):
            $estado   = $asamblea['estado'] ?? (!empty($asamblea['activa']) ? 'activa' : 'cerrada');
            $puedeAdm = esDirectivaDe($perfil, $asamblea['club'] ?? 0);
        ?>
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <h3 style="color: var(--primary-color); margin: 0;">
                            <?= htmlspecialchars($asamblea['titulo'] ?? 'Sin título'); ?>
                        </h3>

                        <?php if ($estado === 'activa'): ?>
                            <span style="background-color: var(--success); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Activa</span>
                        <?php elseif ($estado === 'borrador'): ?>
                            <span style="background-color: #f0ad4e; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Borrador</span>
                        <?php else: ?>
                            <span style="background-color: var(--text-muted); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Cerrada</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($asamblea['club_nombre'])): ?>
                        <p style="color: var(--text-muted); font-size: 14px;">
                            <i class="fa-solid fa-users"></i> <?= htmlspecialchars($asamblea['club_nombre']); ?>
                        </p>
                    <?php endif; ?>

                    <p style="color: var(--text-muted); font-size: 14px;">
                        <i class="fa-solid fa-calendar-days"></i>
                        Cierra: <?= !empty($asamblea['fecha_cierre']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($asamblea['fecha_cierre']))) : 'No definida'; ?>
                    </p>
                </div>

                <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; text-align: right;">
                    <?php if ($puedeAdm): ?>
                        <a href="gestionar_asamblea.php?id=<?= (int) $asamblea['id']; ?>"
                           style="color: var(--text-muted); font-size: 14px; margin-right: 12px; text-decoration: none;">
                            <i class="fa-solid fa-gear"></i> Gestionar
                        </a>
                    <?php endif; ?>

                    <?php if ($estado === 'borrador'): ?>
                        <span style="color: var(--text-muted); font-size: 14px;">Sin abrir</span>
                    <?php elseif ($estado === 'activa' && isset($asambleasVotadas[intval($asamblea['id'])])): ?>
                        <span style="color: var(--success); font-size: 14px; font-weight: bold;">
                            <i class="fa-solid fa-circle-check"></i> Ya votaste
                        </span>
                    <?php elseif ($estado === 'activa'): ?>
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

<?php if ($esDirectivo): ?>
<!-- Modal: crear nueva asamblea -->
<div id="modalAsamblea" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000;">
    <div class="card" style="max-width:480px; width:90%; background:white;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="margin:0; color: var(--primary-color);">Convocar Asamblea</h3>
            <span style="cursor:pointer; font-size:22px; color: var(--text-muted);"
                  onclick="document.getElementById('modalAsamblea').style.display='none'">&times;</span>
        </div>

        <form action="../procesos/asamblea_process.php" method="POST">
            <input type="hidden" name="accion" value="crear_asamblea">

            <label style="display:block; margin-bottom:6px; font-size:14px;">Título de la asamblea</label>
            <input type="text" name="titulo" required placeholder="Ej: Elección de nueva directiva"
                   style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:5px;">

            <label style="display:block; margin-bottom:6px; font-size:14px;">Club</label>
            <select name="club" required
                    style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:5px;">
                <?php foreach ($clubesAdmin as $c): ?>
                    <option value="<?= (int) $c['id']; ?>">
                        <?= htmlspecialchars($c['nombre']); ?><?= !empty($c['siglas']) ? ' [' . htmlspecialchars($c['siglas']) . ']' : ''; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label style="display:block; margin-bottom:6px; font-size:14px;">Fecha y hora de cierre</label>
            <input type="datetime-local" name="fecha_cierre" required
                   style="width:100%; padding:10px; margin-bottom:20px; border:1px solid #ddd; border-radius:5px;">

            <div style="text-align:right;">
                <button type="button" style="margin-right:10px; padding:10px 15px; border:none; background:#eee; border-radius:5px; cursor:pointer;"
                        onclick="document.getElementById('modalAsamblea').style.display='none'">Cancelar</button>
                <button type="submit" class="btn">Crear en borrador</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
