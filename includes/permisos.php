<?php
// ==========================================
// PARTE DE ISAAC: utilidades de permisos
// Consulta GET /api/perfil/ y expone helpers para decidir qué mostrar
// en la interfaz según el rol del usuario en cada club.
// ==========================================

require_once __DIR__ . '/../config/api.php';

/**
 * Devuelve el perfil del usuario autenticado (id, username, is_staff y
 * sus membresías). Se cachea en memoria para no repetir la llamada
 * dentro de una misma carga de página.
 */
function obtenerPerfil()
{
    static $perfil = null;

    if ($perfil === null) {
        $respuesta = callAPI('GET', 'perfil/');
        $perfil = is_array($respuesta) && isset($respuesta['id'])
            ? $respuesta
            : ['id' => null, 'username' => '', 'is_staff' => false, 'membresias' => []];
    }

    return $perfil;
}

/** Lista de clubes donde el usuario tiene rol directivo. */
function clubesQueAdministra($perfil)
{
    if (!empty($perfil['is_staff'])) {
        // El personal de plataforma administra cualquier club.
        $clubes = callAPI('GET', 'clubes/');
        return is_array($clubes) ? $clubes : [];
    }

    $clubes = [];
    foreach ($perfil['membresias'] as $m) {
        if (!empty($m['es_directiva'])) {
            $clubes[] = [
                'id'     => $m['club_id'],
                'nombre' => $m['club_nombre'],
                'siglas' => $m['club_siglas'],
            ];
        }
    }
    return $clubes;
}

/** ¿El usuario puede administrar (crear/cerrar asambleas de) este club? */
function esDirectivaDe($perfil, $clubId)
{
    if (!empty($perfil['is_staff'])) {
        return true;
    }
    foreach ($perfil['membresias'] as $m) {
        if ($m['club_id'] == $clubId && !empty($m['es_directiva'])) {
            return true;
        }
    }
    return false;
}

/** ¿El usuario tiene membresía aprobada (no aspirante) en este club? */
function puedeVotarEn($perfil, $clubId)
{
    foreach ($perfil['membresias'] as $m) {
        if ($m['club_id'] == $clubId) {
            return !empty($m['puede_votar']);
        }
    }
    return false;
}
