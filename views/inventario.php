<?php
require_once '../auth/session_check.php';
require_once '../config/api.php';

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

// Mapeo rápido de ID de club a nombre
$mapaClubes = [];
foreach ($clubes as $club) {
    if (isset($club['id'], $club['nombre'])) {
        $mapaClubes[$club['id']] = $club['nombre'] . ' [' . $club['siglas'] . ']';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventarios - Clubes ESPOL</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">

    <?php include_once '../includes/header.php'; ?>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Inventario de Bienes Tecnológicos</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoItem">
                + Nuevo Ítem
            </button>
        </div>

        <!-- Alertas de estado -->
        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['mensaje_exito']); ?>
                <?php unset($_SESSION['mensaje_exito']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['mensaje_error']); ?>
                <?php unset($_SESSION['mensaje_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Panel de Filtros (Club y Disponibilidad) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="GET" action="inventario.php" class="row g-3 align-items-center">
                    <!-- Filtro por Club -->
                    <div class="col-md-5">
                        <label for="filtroClub" class="form-label fw-bold">Filtrar por Club:</label>
                        <select name="club" id="filtroClub" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Todos los Clubes --</option>
                            <?php foreach ($clubes as $c): ?>
                                <option value="<?= $c['id']; ?>" <?= $filtroClub == $c['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($c['nombre']); ?> (<?= htmlspecialchars($c['siglas']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filtro por Estado/Disponibilidad -->
                    <div class="col-md-5">
                        <label for="filtroEstado" class="form-label fw-bold">Filtrar por Disponibilidad:</label>
                        <select name="estado" id="filtroEstado" class="form-select" onchange="this.form.submit()">
                            <option value="" <?= $filtroEstado === '' ? 'selected' : ''; ?>>-- Todos los Estados --</option>
                            <option value="Disponible" <?= $filtroEstado === 'Disponible' ? 'selected' : ''; ?>>Disponible</option>
                            <option value="Prestado" <?= $filtroEstado === 'Prestado' ? 'selected' : ''; ?>>Prestado</option>
                        </select>
                    </div>

                    <!-- Botón Limpiar -->
                    <div class="col-md-2 d-flex align-items-end">
                        <?php if (!empty($filtroEstado) || !empty($filtroClub)): ?>
                            <a href="inventario.php" class="btn btn-outline-secondary w-100">Limpiar Filtros</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Inventario -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Nombre / Descripción</th>
                                <th>Categoría</th>
                                <th>Club Perteneciente</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inventario)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No se encontraron ítems con los criterios de búsqueda seleccionados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($inventario as $item): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($item['codigo']); ?></strong></td>
                                        <td><?= htmlspecialchars($item['nombre']); ?></td>
                                        <td><?= htmlspecialchars($item['categoria']); ?></td>
                                        <td><?= htmlspecialchars($mapaClubes[$item['club']] ?? 'Club #' . $item['club']); ?></td>
                                        <td>
                                            <?php if ($item['estado'] === 'Disponible'): ?>
                                                <span class="badge bg-success">Disponible</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Prestado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <form action="../procesos/inventario_process.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este ítem?');" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="item_id" value="<?= $item['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Nuevo Ítem -->
    <div class="modal fade" id="modalNuevoItem" tabindex="-1" aria-labelledby="modalNuevoItemLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="../procesos/inventario_process.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNuevoItemLabel">Registrar Ítem de Inventario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código del Bien</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" required placeholder="Ej. INV-2026-001">
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre o Descripción</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Ej. Proyector Epson HD">
                        </div>
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría</label>
                            <input type="text" class="form-control" id="categoria" name="categoria" required placeholder="Ej. Audiovisual, Cómputo">
                        </div>
                        <div class="mb-3">
                            <label for="club" class="form-label">Club Asignado</label>
                            <select class="form-select" id="club" name="club" required>
                                <option value="">-- Selecciona un club --</option>
                                <?php foreach ($clubes as $c): ?>
                                    <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['nombre']); ?> (<?= htmlspecialchars($c['siglas']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado Inicial</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="Disponible">Disponible</option>
                                <option value="Prestado">Prestado</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Ítem</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>