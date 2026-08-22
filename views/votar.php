<?php
require_once '../auth/session_check.php';
require_once '../config/api.php';

$asambleaId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Detalle de la asamblea (GET /api/asambleas/{id}/)
$asamblea = callAPI('GET', 'asambleas/' . $asambleaId . '/');

if (!isset($asamblea['id'])) {
    header('Location: asambleas.php');
    exit();
}

// Opciones de la asamblea. El backend ya soporta el filtro ?asamblea=, así que
// no hace falta descargar todas las opciones del sistema y filtrarlas en PHP.
$opciones = callAPI('GET', 'opciones-voto/', ['asamblea' => $asambleaId]);
$opciones = is_array($opciones) ? $opciones : [];

// Verificar si el usuario actual ya emitió su voto en esta asamblea.
// GET /api/votos/ solo devuelve los votos del propio solicitante, por lo que
// esta consulta no revela información de terceros.
$misVotos = callAPI('GET', 'votos/', ['asamblea' => $asambleaId]);
$yaVoto = is_array($misVotos) && count($misVotos) > 0;

// Si la asamblea está cerrada, el conteo lo calcula el backend con
// annotate(Count(...)). El frontend solo presenta el resultado ya agregado.
$resultados = [];
$totalVotos = 0;

if (empty($asamblea['activa'])) {
    $data = callAPI('GET', 'asambleas/' . $asambleaId . '/resultados/');
    if (isset($data['resultados']) && is_array($data['resultados'])) {
        $resultados  = $data['resultados'];
        $totalVotos  = intval($data['total_votos'] ?? 0);
    }
}
?>

<?php include '../includes/header.php'; ?>

<div style="margin-bottom: 20px;">
    <a href="asambleas.php" style="color: var(--primary-color); text-decoration: none; font-size: 14px;">
        <i class="fa-solid fa-arrow-left"></i> Volver a Asambleas
    </a>
</div>

<div class="card">
    <h2 style="color: var(--primary-color); margin-bottom: 5px;">
        <?= htmlspecialchars($asamblea['titulo'] ?? 'Asamblea'); ?>
    </h2>
    <p style="color: var(--text-muted); margin-bottom: 20px;">
        Estado: <strong><?= !empty($asamblea['activa']) ? 'Activa' : 'Cerrada'; ?></strong>
        &nbsp;|&nbsp; Cierra: <?= !empty($asamblea['fecha_cierre']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($asamblea['fecha_cierre']))) : 'No definida'; ?>
    </p>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
        <div style="border-left: 4px solid var(--danger); padding: 10px; margin-bottom: 20px; color: var(--danger);">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_SESSION['mensaje_error']); ?>
        </div>
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>

    <?php if (!empty($asamblea['activa'])): ?>

        <!-- ===================== PAPELETA DE VOTACIÓN ===================== -->
        <?php if ($yaVoto): ?>
            <div style="border-left: 4px solid var(--success); padding: 15px; color: var(--success);">
                <i class="fa-solid fa-circle-check"></i>
                Ya emitiste tu voto en esta asamblea. Solo se permite un voto por usuario.
                Los resultados se publicarán cuando la directiva cierre la votación.
            </div>
        <?php elseif (empty($opciones)): ?>
            <p style="color: var(--text-muted);">Esta asamblea todavía no tiene opciones de votación registradas.</p>
        <?php else: ?>
            <form action="../procesos/voto_process.php" method="POST">
                <input type="hidden" name="asamblea_id" value="<?= (int) $asamblea['id']; ?>">

                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; margin-bottom: 25px;">
                    <?php foreach ($opciones as $op): ?>
                        <label class="card" style="cursor: pointer; text-align: center; display: block; margin-bottom: 0;">
                            <input type="radio" name="opcion_id" value="<?= (int) $op['id']; ?>" required style="margin-bottom: 10px;">
                            <div><i class="fa-solid fa-users fa-2x" style="color: var(--secondary-color);"></i></div>
                            <strong style="display: block; margin-top: 10px;">
                                <?= htmlspecialchars($op['nombre_lista']); ?>
                            </strong>
                            <?php if (!empty($op['descripcion'])): ?>
                                <p style="font-size: 13px; color: var(--text-muted); margin-top: 5px;">
                                    <?= htmlspecialchars($op['descripcion']); ?>
                                </p>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="btn">
                    <i class="fa-solid fa-check-to-slot"></i> Emitir Voto
                </button>
            </form>
        <?php endif; ?>

    <?php else: ?>

        <!-- ===================== RESULTADOS (asamblea cerrada) ===================== -->
        <h3 style="margin-bottom: 15px;">Resultados</h3>

        <?php if (empty($resultados)): ?>
            <p style="color: var(--text-muted);">Esta asamblea no tuvo opciones de votación registradas.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--background-light); text-align: left;">
                        <th style="padding: 10px;">Opción</th>
                        <th style="padding: 10px;">Votos</th>
                        <th style="padding: 10px;">%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $r): ?>
                        <tr style="border-bottom: 1px solid var(--background-light);">
                            <td style="padding: 10px;"><?= htmlspecialchars($r['nombre_lista']); ?></td>
                            <td style="padding: 10px;"><?= (int) $r['votos']; ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($r['porcentaje']); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top: 15px; color: var(--text-muted); font-size: 14px;">
                Total de votos emitidos: <?= (int) $totalVotos; ?>
            </p>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>