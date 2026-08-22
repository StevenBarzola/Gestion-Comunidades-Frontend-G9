<?php
// ==========================================
// PARTE DE ISAAC: panel de gestión de una asamblea (solo directiva)
// Permite armar la papeleta, abrir la votación y cerrarla.
// ==========================================

require_once '../auth/session_check.php';
require_once '../config/api.php';
require_once '../includes/permisos.php';

$asambleaId = intval($_GET['id'] ?? 0);
if (!$asambleaId) {
    header('Location: asambleas.php');
    exit();
}

$asamblea = callAPI('GET', 'asambleas/' . $asambleaId . '/');
if (!isset($asamblea['id'])) {
    $_SESSION['mensaje_error'] = "No se encontró la asamblea solicitada.";
    header('Location: asambleas.php');
    exit();
}

$perfil = obtenerPerfil();
if (!esDirectivaDe($perfil, $asamblea['club'])) {
    $_SESSION['mensaje_error'] = "Solo la directiva del club puede gestionar esta asamblea.";
    header('Location: asambleas.php');
    exit();
}

$opciones = callAPI('GET', 'opciones-voto/', ['asamblea' => $asambleaId]);
$opciones = is_array($opciones) ? $opciones : [];

$estado    = $asamblea['estado'] ?? (!empty($asamblea['activa']) ? 'activa' : 'cerrada');
$editable  = ($estado === 'borrador');   // la papeleta solo se toca antes de abrir
$puedeAbrir = $editable && count($opciones) >= 2;
?>

<?php include '../includes/header.php'; ?>

<div style="margin-bottom: 20px;">
    <a href="asambleas.php" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
        <i class="fa-solid fa-arrow-left"></i> Volver a Asambleas
    </a>
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

<!-- Cabecera de la asamblea -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
            <h2 style="color: var(--primary-color); margin:0 0 8px 0;">
                <i class="fa-solid fa-gear"></i> <?= htmlspecialchars($asamblea['titulo']); ?>
            </h2>
            <?php if (!empty($asamblea['club_nombre'])): ?>
                <p style="color: var(--text-muted); font-size:14px; margin:0 0 4px 0;">
                    <i class="fa-solid fa-users"></i> <?= htmlspecialchars($asamblea['club_nombre']); ?>
                </p>
            <?php endif; ?>
            <p style="color: var(--text-muted); font-size:14px; margin:0;">
                <i class="fa-solid fa-calendar-days"></i>
                Cierra: <?= !empty($asamblea['fecha_cierre']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($asamblea['fecha_cierre']))) : 'No definida'; ?>
            </p>
        </div>

        <?php if ($estado === 'activa'): ?>
            <span style="background-color: var(--success); color:white; padding:6px 12px; border-radius:4px; font-size:13px; font-weight:bold;">Votación abierta</span>
        <?php elseif ($estado === 'borrador'): ?>
            <span style="background-color:#f0ad4e; color:white; padding:6px 12px; border-radius:4px; font-size:13px; font-weight:bold;">Borrador</span>
        <?php else: ?>
            <span style="background-color: var(--text-muted); color:white; padding:6px 12px; border-radius:4px; font-size:13px; font-weight:bold;">Cerrada</span>
        <?php endif; ?>
    </div>
</div>

<!-- Papeleta -->
<div class="card">
    <h3 style="margin-top:0; color: var(--text-dark);">
        <i class="fa-solid fa-list-check"></i> Opciones de la papeleta
        <span style="color: var(--text-muted); font-weight:normal; font-size:14px;">(<?= count($opciones); ?>)</span>
    </h3>

    <?php if (!$editable): ?>
        <p style="color: var(--text-muted); font-size:14px; font-style:italic;">
            La papeleta ya no puede modificarse porque la votación fue abierta.
        </p>
    <?php endif; ?>

    <?php if (count($opciones) > 0): ?>
        <table style="width:100%; border-collapse:collapse; margin-bottom:15px;">
            <thead>
                <tr style="border-bottom:2px solid var(--background-light); text-align:left;">
                    <th style="padding:10px;">Opción</th>
                    <th style="padding:10px;">Descripción</th>
                    <?php if ($editable): ?><th style="padding:10px; width:110px;"></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($opciones as $o): ?>
                    <tr style="border-bottom:1px solid var(--background-light);">
                        <td style="padding:10px; font-weight:bold;"><?= htmlspecialchars($o['nombre_lista']); ?></td>
                        <td style="padding:10px; color: var(--text-muted); font-size:14px;">
                            <?= htmlspecialchars($o['descripcion'] ?? ''); ?>
                        </td>
                        <?php if ($editable): ?>
                            <td style="padding:10px;">
                                <form action="../procesos/asamblea_process.php" method="POST"
                                      onsubmit="return confirm('¿Eliminar esta opción de la papeleta?');">
                                    <input type="hidden" name="accion" value="eliminar_opcion">
                                    <input type="hidden" name="asamblea_id" value="<?= (int) $asambleaId; ?>">
                                    <input type="hidden" name="opcion_id" value="<?= (int) $o['id']; ?>">
                                    <button type="submit" class="btn" style="background-color: var(--danger); padding:5px 10px; font-size:13px;">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: var(--text-muted);">Todavía no hay opciones registradas.</p>
    <?php endif; ?>

    <?php if ($editable): ?>
        <form action="../procesos/asamblea_process.php" method="POST"
              style="border-top:1px solid #eee; padding-top:15px; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            <input type="hidden" name="accion" value="crear_opcion">
            <input type="hidden" name="asamblea_id" value="<?= (int) $asambleaId; ?>">

            <div style="flex:1; min-width:180px;">
                <label style="display:block; font-size:13px; margin-bottom:5px;">Nombre de la opción</label>
                <input type="text" name="nombre_lista" required placeholder="Ej: Lista A - Innovación"
                       style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
            </div>
            <div style="flex:2; min-width:220px;">
                <label style="display:block; font-size:13px; margin-bottom:5px;">Descripción (opcional)</label>
                <input type="text" name="descripcion" placeholder="Breve detalle de la propuesta"
                       style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
            </div>
            <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Agregar</button>
        </form>
    <?php endif; ?>
</div>

<!-- Acciones sobre el estado de la votación -->
<div class="card">
    <h3 style="margin-top:0; color: var(--text-dark);"><i class="fa-solid fa-flag"></i> Estado de la votación</h3>

    <?php if ($estado === 'borrador'): ?>
        <p style="color: var(--text-muted); font-size:14px;">
            La asamblea está en borrador: los miembros todavía no pueden votar.
            <?php if (!$puedeAbrir): ?>
                Necesitas al menos <strong>2 opciones</strong> para poder abrir la votación.
            <?php endif; ?>
        </p>
        <form action="../procesos/asamblea_process.php" method="POST"
              onsubmit="return confirm('Al abrir la votación la papeleta queda bloqueada. ¿Continuar?');">
            <input type="hidden" name="accion" value="activar">
            <input type="hidden" name="asamblea_id" value="<?= (int) $asambleaId; ?>">
            <button type="submit" class="btn" <?= $puedeAbrir ? '' : 'disabled style="opacity:0.5; cursor:not-allowed;"'; ?>>
                <i class="fa-solid fa-lock-open"></i> Abrir votación
            </button>
        </form>

    <?php elseif ($estado === 'activa'): ?>
        <p style="color: var(--text-muted); font-size:14px;">
            La votación está en curso. Al cerrarla se publican los resultados y
            <strong>no podrá reabrirse</strong>.
        </p>
        <form action="../procesos/asamblea_process.php" method="POST"
              onsubmit="return confirm('Cerrar la votación es irreversible. ¿Continuar?');">
            <input type="hidden" name="accion" value="cerrar">
            <input type="hidden" name="asamblea_id" value="<?= (int) $asambleaId; ?>">
            <button type="submit" class="btn" style="background-color: var(--danger);">
                <i class="fa-solid fa-lock"></i> Cerrar votación
            </button>
        </form>

    <?php else: ?>
        <p style="color: var(--text-muted); font-size:14px;">
            La votación está cerrada y los resultados ya son públicos para los miembros del club.
        </p>
        <a href="votar.php?id=<?= (int) $asambleaId; ?>" class="btn" style="background-color: var(--text-muted);">
            <i class="fa-solid fa-chart-simple"></i> Ver resultados
        </a>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
