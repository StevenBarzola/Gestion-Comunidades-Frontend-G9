<?php
// ==========================================
// Panel de gestión de integrantes de un club (solo directiva)
// Aprobar aspirantes, asignar roles internos y dar de baja.
// ==========================================

require_once '../auth/session_check.php';
require_once '../config/api.php';
require_once '../includes/permisos.php';

$clubId = intval($_GET['club'] ?? 0);
if (!$clubId) {
    header('Location: directorio.php');
    exit();
}

$club = callAPI('GET', 'clubes/' . $clubId . '/');
if (!isset($club['id'])) {
    $_SESSION['mensaje_error'] = "No se encontró el club solicitado.";
    header('Location: directorio.php');
    exit();
}

$perfil = obtenerPerfil();
if (!esDirectivaDe($perfil, $clubId)) {
    $_SESSION['mensaje_error'] = "Solo la directiva del club puede gestionar a sus integrantes.";
    header('Location: detalle_club.php?id=' . $clubId);
    exit();
}

$miembros = callAPI('GET', 'membresias/', ['club' => $clubId]);
$miembros = is_array($miembros) ? $miembros : [];

// Separar solicitudes pendientes de integrantes ya aprobados
$aspirantes = array_values(array_filter($miembros, fn($m) => ($m['rol'] ?? '') === 'aspirante'));
$activos    = array_values(array_filter($miembros, fn($m) => ($m['rol'] ?? '') !== 'aspirante'));

$ROLES = [
    'presidente'    => 'Presidente',
    'vicepresidente' => 'Vicepresidente',
    'secretario'    => 'Secretario',
    'coor_eventos'  => 'Coordinador de Eventos',
    'coor_rsocial'  => 'Coordinador de Redes Sociales',
    'miembro'       => 'Miembro',
    'aspirante'     => 'Aspirante',
];
?>

<?php include '../includes/header.php'; ?>

<div style="margin-bottom: 20px;">
    <a href="detalle_club.php?id=<?= (int) $clubId; ?>" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
        <i class="fa-solid fa-arrow-left"></i> Volver al club
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

<h2 style="color: var(--primary-color);">
    <i class="fa-solid fa-users-gear"></i> Integrantes de <?= htmlspecialchars($club['nombre']); ?>
</h2>

<!-- ================= SOLICITUDES PENDIENTES ================= -->
<div class="card">
    <h3 style="margin-top: 0; color: var(--text-dark);">
        <i class="fa-solid fa-user-clock"></i> Solicitudes pendientes
        <span style="color: var(--text-muted); font-weight: normal; font-size: 14px;">(<?= count($aspirantes); ?>)</span>
    </h3>

    <?php if (count($aspirantes) > 0): ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--background-light);">
                    <th style="padding: 10px;">Usuario</th>
                    <th style="padding: 10px;">Solicitó el</th>
                    <th style="padding: 10px; width: 220px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aspirantes as $m): ?>
                    <tr style="border-bottom: 1px solid var(--background-light);">
                        <td style="padding: 10px; font-weight: bold;">
                            <?= htmlspecialchars($m['usuario_username'] ?? 'Usuario'); ?>
                        </td>
                        <td style="padding: 10px; color: var(--text-muted); font-size: 14px;">
                            <?= !empty($m['fecha_ingreso']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($m['fecha_ingreso']))) : '—'; ?>
                        </td>
                        <td style="padding: 10px; display: flex; gap: 8px;">
                            <form action="../procesos/miembros_process.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="accion" value="aprobar">
                                <input type="hidden" name="club_id" value="<?= (int) $clubId; ?>">
                                <input type="hidden" name="membresia_id" value="<?= (int) $m['id']; ?>">
                                <button type="submit" class="btn" style="padding: 6px 12px; font-size: 13px;">
                                    <i class="fa-solid fa-check"></i> Aprobar
                                </button>
                            </form>
                            <form action="../procesos/miembros_process.php" method="POST" style="margin: 0;"
                                  onsubmit="return confirm('¿Rechazar esta solicitud de ingreso?');">
                                <input type="hidden" name="accion" value="expulsar">
                                <input type="hidden" name="club_id" value="<?= (int) $clubId; ?>">
                                <input type="hidden" name="membresia_id" value="<?= (int) $m['id']; ?>">
                                <button type="submit" class="btn" style="background-color: var(--danger); padding: 6px 12px; font-size: 13px;">
                                    <i class="fa-solid fa-xmark"></i> Rechazar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color: var(--text-muted); font-size: 14px;">No hay solicitudes de ingreso pendientes.</p>
    <?php endif; ?>
</div>

<!-- ================= INTEGRANTES ACTIVOS ================= -->
<div class="card">
    <h3 style="margin-top: 0; color: var(--text-dark);">
        <i class="fa-solid fa-users"></i> Integrantes activos
        <span style="color: var(--text-muted); font-weight: normal; font-size: 14px;">(<?= count($activos); ?>)</span>
    </h3>

    <?php if (count($activos) > 0): ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--background-light);">
                    <th style="padding: 10px;">Usuario</th>
                    <th style="padding: 10px;">Ingreso</th>
                    <th style="padding: 10px; width: 300px;">Rol en el club</th>
                    <th style="padding: 10px; width: 110px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activos as $m): ?>
                    <tr style="border-bottom: 1px solid var(--background-light);">
                        <td style="padding: 10px; font-weight: bold;">
                            <?= htmlspecialchars($m['usuario_username'] ?? 'Usuario'); ?>
                        </td>
                        <td style="padding: 10px; color: var(--text-muted); font-size: 14px;">
                            <?= !empty($m['fecha_ingreso']) ? htmlspecialchars(date('d/m/Y', strtotime($m['fecha_ingreso']))) : '—'; ?>
                        </td>
                        <td style="padding: 10px;">
                            <form action="../procesos/miembros_process.php" method="POST"
                                  style="margin: 0; display: flex; gap: 8px; align-items: center;">
                                <input type="hidden" name="accion" value="cambiar_rol">
                                <input type="hidden" name="club_id" value="<?= (int) $clubId; ?>">
                                <input type="hidden" name="membresia_id" value="<?= (int) $m['id']; ?>">
                                <select name="rol" style="flex: 1; padding: 7px; border: 1px solid #ddd; border-radius: 5px;">
                                    <?php foreach ($ROLES as $valor => $etiqueta): ?>
                                        <option value="<?= $valor; ?>" <?= ($m['rol'] ?? '') === $valor ? 'selected' : ''; ?>>
                                            <?= $etiqueta; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn" style="padding: 6px 12px; font-size: 13px;">
                                    Guardar
                                </button>
                            </form>
                        </td>
                        <td style="padding: 10px;">
                            <form action="../procesos/miembros_process.php" method="POST" style="margin: 0;"
                                  onsubmit="return confirm('¿Dar de baja a este integrante del club?');">
                                <input type="hidden" name="accion" value="expulsar">
                                <input type="hidden" name="club_id" value="<?= (int) $clubId; ?>">
                                <input type="hidden" name="membresia_id" value="<?= (int) $m['id']; ?>">
                                <button type="submit" class="btn" style="background-color: var(--danger); padding: 6px 12px; font-size: 13px;">
                                    Dar de baja
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="color: var(--text-muted); font-size: 13px; margin-top: 15px; font-style: italic;">
            Un club no puede quedarse sin presidente: para cambiar de presidencia, nombra primero al nuevo presidente.
        </p>
    <?php else: ?>
        <p style="color: var(--text-muted); font-size: 14px;">Este club todavía no tiene integrantes activos.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
