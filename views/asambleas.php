<?php
require_once '../auth/session_check.php';
require_once '../config/api.php';

// Consumir el endpoint de asambleas del backend
$asambleas = callAPI('GET', 'asambleas/');
$asambleas = is_array($asambleas) ? $asambleas : [];
?>

<?php include '../includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: var(--primary-color);"><i class="fa-solid fa-check-to-slot"></i> Asambleas y Votaciones</h2>
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
        <?php foreach ($asambleas as $asamblea): ?>
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <h3 style="color: var(--primary-color); margin: 0;">
                            <?= htmlspecialchars($asamblea['titulo'] ?? 'Sin título'); ?>
                        </h3>

                        <?php if (!empty($asamblea['activa'])): ?>
                            <span style="background-color: var(--success); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                Activa
                            </span>
                        <?php else: ?>
                            <span style="background-color: var(--text-muted); color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                Cerrada
                            </span>
                        <?php endif; ?>
                    </div>

                    <p style="color: var(--text-muted); font-size: 14px;">
                        <i class="fa-solid fa-calendar-days"></i>
                        Cierra: <?= htmlspecialchars($asamblea['fecha_cierre'] ?? 'No definida'); ?>
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

<?php include '../includes/footer.php'; ?>