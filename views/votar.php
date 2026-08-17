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

// El endpoint de opciones-voto no filtra por asamblea, así que se filtra aquí en PHP
$todasOpciones = callAPI('GET', 'opciones-voto/');
$todasOpciones = is_array($todasOpciones) ? $todasOpciones : [];
$opciones = array_values(array_filter($todasOpciones, function ($o) use ($asambleaId) {
    return isset($o['asamblea']) && intval($o['asamblea']) === $asambleaId;
}));

// Si la asamblea está cerrada, tabulamos los resultados a partir de /api/votos/
$resultados = [];
$totalVotos = 0;

if (empty($asamblea['activa'])) {
    $todosVotos = callAPI('GET', 'votos/');
    $todosVotos = is_array($todosVotos) ? $todosVotos : [];
    $votosAsamblea = array_filter($todosVotos, function ($v) use ($asambleaId) {
        return isset($v['asamblea']) && intval($v['asamblea']) === $asambleaId;
    });

    foreach ($opciones as $op) {
        $resultados[$op['id']] = [
            'nombre_lista' => $op['nombre_lista'],
            'votos' => 0,
        ];
    }
    foreach ($votosAsamblea as $v) {
        if (isset($resultados[$v['opcion']])) {
            $resultados[$v['opcion']]['votos']++;
            $totalVotos++;
        }
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
        &nbsp;|&nbsp; Cierra: <?= htmlspecialchars($asamblea['fecha_cierre'] ?? 'No definida'); ?>
    </p>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
        <div style="border-left: 4px solid var(--danger); padding: 10px; margin-bottom: 20px; color: var(--danger);">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_SESSION['mensaje_error']); ?>
        </div>
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>

    <?php if (!empty($asamblea['activa'])): ?>

        <!-- ===================== PAPELETA DE VOTACIÓN ===================== -->
        <?php if (empty($opciones)): ?>
            <p style="color: var(--text-muted);">Esta asamblea todavía no tiene opciones de votación registradas.</p>
        <?php else: ?>
            <form action="../procesos/voto_process.php" method="POST">
                <input type="hidden" name="asamblea_id" value="<?= (int) $asamblea['id']; ?>">

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px;">
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
                            <td style="padding: 10px;">
                                <?= $totalVotos > 0 ? round(($r['votos'] / $totalVotos) * 100, 1) : 0; ?>%
                            </td>
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