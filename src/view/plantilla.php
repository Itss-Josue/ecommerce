<?php
session_start();

// Cargar configuración - Rutas corregidas
require_once __DIR__ . "/../config/config.php";

// Si las constantes no están definidas, definirlas temporalmente
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8888/token/');
}
if (!defined('BASE_URL_SERVER')) {
    define('BASE_URL_SERVER', 'http://localhost:8888/token/');
}
if (!defined('RUTA_API')) {
    define('RUTA_API', 'http://localhost:8888/token/');
}

require_once "./src/control/vistas_control.php";

$mostrar = new vistasControlador();
$vista = $mostrar->obtenerVistaControlador();
$reset = '';

if ($vista == "reset-password") {
    $reset = "reset-password";
}

// Verificar que las constantes críticas estén definidas
if (!defined('BASE_URL_SERVER')) {
    die("Error: Configuración no cargada correctamente. BASE_URL_SERVER no está definida.");
}

if (isset($_SESSION['sesion_id']) && isset($_SESSION['sesion_token'])) {

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER . "src/control/Sesion.php?tipo=validar_sesion&sesion=" . $_SESSION['sesion_id'] . "&token=" . $_SESSION['sesion_token'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: " . BASE_URL_SERVER,
            "x-rapidapi-key: XXXX"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    }
    
    if (!$response) {
        $vista = "login";
    }
}

if ($reset == "reset-password") {
    $vista = "reset-password";
}

// Lista de vistas que no requieren header/footer (vistas completas)
$vistas_completas = [
    "login", 
    "404", 
    "reset-password", 
    "apiestudiante",  // Ahora como string simple
    "apiwebcon"       // Nueva vista API
];

// Determinar qué vistas cargar
if (in_array($vista, $vistas_completas) || 
    $vista == "./src/view/apiestudiante.php" || 
    $vista == "./src/view/apiwebcon.php") {
    
    // Para vistas API, cargar directamente el archivo
    if ($vista == "apiestudiante" || $vista == "apiwebcon") {
        require_once "./src/view/" . $vista . ".php";
    } 
    // Para vistas completas existentes
    else if (in_array($vista, $vistas_completas)) {
        require_once "./src/view/" . $vista . ".php";
    }
    // Para rutas completas de archivo (backward compatibility)
    else {
        require_once $vista;
    }
} else {
    // Vistas normales con header y footer
    if (!in_array($vista, ['./src/view/imprimir-movimiento.php', './src/view/reporte-bienes.php'])) {
        include "./src/view/include/header.php";
    }
    
    // Cargar la vista principal
    if (file_exists($vista)) {
        include $vista;
    } else if (file_exists("./src/view/" . $vista . ".php")) {
        include "./src/view/" . $vista . ".php";
    } else {
        include "./src/view/404.php";
    }
    
    if (!in_array($vista, ['./src/view/imprimir-movimiento.php', './src/view/reporte-bienes.php'])) {
        include "./src/view/include/footer.php";
    }
}