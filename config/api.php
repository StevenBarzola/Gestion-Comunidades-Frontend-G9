<?php
// Iniciar la sesión de PHP de forma global
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL base de tu backend en Django (NOTA: Cambiarla por la de tu CodeSpace)
define('API_BASE_URL', 'https://ominous-xylophone-wrrvj5ppqp9725jwq-8003.app.github.dev/api/');

// Función helper para consumir la API fácilmente usando cURL
function callAPI($method, $endpoint, $data = false) {
    $curl = curl_init();
    $url = API_BASE_URL . $endpoint;

    // Configuración base de la cabecera
    $headers = ['Content-Type: application/json'];

    // Si existe un token en la sesión de PHP, lo adjuntamos como llave de seguridad
    if (isset($_SESSION['api_token'])) {
        $headers[] = 'Authorization: Token ' . $_SESSION['api_token'];
    }

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        //Aumento del caso 'DELETE'
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        default:
            if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result, true);
}
?>