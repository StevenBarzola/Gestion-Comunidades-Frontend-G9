<?php
require_once '../auth/session_check.php';
require_once '../config/api.php';

// Validar que venga un ID en la URL
if (!isset($_GET['id'])) {
    header('Location: directorio.php');
    exit();
}

// Consumir el club específico
$id_club = $_GET['id'];
$club = callAPI('GET', "clubes/$id_club/");

if (isset($club['detail']) && $club['detail'] == 'No encontrado.') {
    header('Location: directorio.php');
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<div class="card" style="max-width: 800px; margin: 0 auto; padding: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--primary-color); padding-bottom: 15px; margin-bottom: 20px;">
        <div>
            <h2 style="color: var(--primary-color); margin: 0 0 10px 0;">
                <?= htmlspecialchars($club['nombre'] ?? 'Club Desconocido'); ?>
            </h2>
            <span style="background-color: var(--secondary-color); color: white; padding: 5px 10px; border-radius: 4px; font-weight: bold;">
                <?= htmlspecialchars($club['siglas'] ?? 'S/N'); ?>
            </span>
        </div>
        
        <!-- Botón deshabilitado intencionalmente por alcance del sprint -->
        <button class="btn" style="background-color: var(--text-muted); cursor: not-allowed;" title="Funcionalidad programada para la siguiente fase">
            <i class="fa-solid fa-user-plus"></i> Unirse al Club
        </button>
    </div>

    <div style="margin-bottom: 20px;">
        <p style="color: var(--text-muted); font-size: 16px;">
            <i class="fa-solid fa-building"></i> <strong>Facultad:</strong> <?= htmlspecialchars($club['facultad'] ?? 'No especificada'); ?>
        </p>
    </div>

    <div>
        <h4 style="color: var(--text-dark); margin-bottom: 10px;">Sobre nosotros</h4>
        <p style="line-height: 1.6; color: #555; font-size: 15px;">
            <?= nl2br(htmlspecialchars($club['descripcion'] ?? 'Este club aún no ha proporcionado una descripción.')); ?>
        </p>
    </div>

    <div style="margin-top: 30px; text-align: left;">
        <a href="directorio.php" style="color: var(--text-muted); text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Volver al directorio
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>