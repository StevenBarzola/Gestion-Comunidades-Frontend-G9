<?php
require_once '../auth/session_check.php';
require_once '../config/api.php';
require_once '../includes/permisos.php';

if (!isset($_GET['id'])) {
    header('Location: directorio.php');
    exit();
}

$id_club = $_GET['id'];
$club = callAPI('GET', "clubes/$id_club/");

if (isset($club['detail']) && $club['detail'] == 'No encontrado.') {
    header('Location: directorio.php');
    exit();
}

// Estado del usuario actual respecto a este club
$perfil    = obtenerPerfil();
$esDirect  = esDirectivaDe($perfil, $id_club);
$miRol     = null;
foreach ($perfil['membresias'] as $m) {
    if ($m['club_id'] == $id_club) { $miRol = $m['rol']; break; }
}
?>

<?php include '../includes/header.php'; ?>

<div class="card" style="max-width: 800px; margin: 0 auto; padding: 30px;">
    
    <!-- Alertas -->
    <?php if (isset($_SESSION['mensaje_membresia'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_SESSION['mensaje_membresia']); ?>
            <?php unset($_SESSION['mensaje_membresia']); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--primary-color); padding-bottom: 15px; margin-bottom: 20px;">
        <div>
            <h2 style="color: var(--primary-color); margin: 0 0 10px 0;">
                <?= htmlspecialchars($club['nombre'] ?? 'Club Desconocido'); ?>
            </h2>
            <span style="background-color: var(--secondary-color); color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold;">
                <?= htmlspecialchars($club['siglas'] ?? 'S/N'); ?>
            </span>
        </div>
        
        <!-- Acciones contextuales según el rol del usuario en este club -->
        <div style="display: flex; gap: 10px; align-items: center;">
            <?php if ($esDirect): ?>
                <a href="gestionar_miembros.php?club=<?= (int) $id_club; ?>" class="btn">
                    <i class="fa-solid fa-users-gear"></i> Gestionar integrantes
                </a>
            <?php endif; ?>

            <?php if ($miRol === null): ?>
                <form action="../procesos/membresia_process.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="club_id" value="<?= htmlspecialchars($id_club); ?>">
                    <button type="submit" class="btn" style="background-color: var(--primary-color);">
                        <i class="fa-solid fa-user-plus"></i> Unirse al Club
                    </button>
                </form>
            <?php elseif ($miRol === 'aspirante'): ?>
                <span style="background-color: #f0ad4e; color: white; padding: 8px 14px; border-radius: 4px; font-size: 14px; font-weight: bold;">
                    <i class="fa-solid fa-hourglass-half"></i> Solicitud pendiente
                </span>
            <?php else: ?>
                <span style="background-color: var(--success); color: white; padding: 8px 14px; border-radius: 4px; font-size: 14px; font-weight: bold;">
                    <i class="fa-solid fa-circle-check"></i> Ya eres parte del club
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-bottom: 20px;">
        <p style="color: var(--text-muted); font-size: 16px;">
            <i class="fa-solid fa-building"></i> <strong>Facultad:</strong> <?= htmlspecialchars($club['facultad'] ?? 'No especificada'); ?>
        </p>
    </div>

    <div style="margin-bottom: 30px;">
        <h4 style="color: var(--text-dark); margin-bottom: 10px;">Sobre nosotros</h4>
        <p style="line-height: 1.6; color: #555; font-size: 15px;">
            <?= nl2br(htmlspecialchars($club['descripcion'] ?? 'Sin descripción.')); ?>
        </p>
    </div>

    <!-- Nueva Sección: Lista de Miembros -->
    <div>
        <h4 style="color: var(--primary-color); border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
            <i class="fa-solid fa-users-viewfinder"></i> Integrantes de la Agrupación
        </h4>
        
        <?php if (isset($club['miembros']) && is_array($club['miembros']) && count($club['miembros']) > 0): ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 15px;">
                <thead>
                    <tr style="border-bottom: 2px solid #ddd;">
                        <th style="padding: 10px;">Usuario</th>
                        <th style="padding: 10px;">Rol asignado</th>
                        <th style="padding: 10px;">Fecha de ingreso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($club['miembros'] as $miembro): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><strong><?= htmlspecialchars($miembro['usuario_username'] ?? 'Anónimo'); ?></strong></td>
                            <td style="padding: 10px;">
                                <span style="background-color: #e9ecef; color: #495057; padding: 4px 8px; border-radius: 4px; font-size: 12px; text-transform: uppercase;">
                                    <?= htmlspecialchars($miembro['rol'] ?? 'Aspirante'); ?>
                                </span>
                            </td>
                            <td style="padding: 10px; color: var(--text-muted);"><?= htmlspecialchars(date('d/m/Y', strtotime($miembro['fecha_ingreso'] ?? 'now'))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: var(--text-muted); font-size: 14px; text-align: center; padding: 20px;">
                Aún no hay miembros registrados en este club. ¡Sé el primero en unirte!
            </p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 30px; text-align: left;">
        <a href="directorio.php" style="color: var(--text-muted); text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Volver al directorio
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>