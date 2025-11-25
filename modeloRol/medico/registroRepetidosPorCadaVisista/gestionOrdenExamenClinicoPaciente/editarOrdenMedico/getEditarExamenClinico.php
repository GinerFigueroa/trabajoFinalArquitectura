<?php

session_start();

include_once('./controlEditarExamenClinico.php'); // Incluye el controlador con las clases Command y Factory
include_once('../../../../../shared/mensajeSistema.php');

$objControl = new controlEditarExamenClinico();
$objMensaje = new mensajeSistema();
$urlRetorno = "../indexOrdenExamenClinico.php";

// Validar método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $objMensaje->mensajeSistemaShow("Método no permitido", $urlRetorno, "error");
    exit();
}

// Recoger y validar datos del formulario
$idOrden = isset($_POST['id_orden']) ? (int)$_POST['id_orden'] : null;

if (!$idOrden) {
    $objMensaje->mensajeSistemaShow("ID de orden no válido", $urlRetorno, "error");
    exit();
}

// Datos pasados al Comando
// Atributo: $datos
$datos = [
    'idOrden' => $idOrden,
    'historiaClinicaId' => isset($_POST['historia_clinica_id']) ? (int)$_POST['historia_clinica_id'] : null,
    'fecha' => isset($_POST['fecha']) ? trim($_POST['fecha']) : '',
    'tipoExamen' => isset($_POST['tipo_examen']) ? trim($_POST['tipo_examen']) : '',
    'indicaciones' => isset($_POST['indicaciones']) ? trim($_POST['indicaciones']) : '',
    'estado' => isset($_POST['estado']) ? trim($_POST['estado']) : '',
    'resultados' => isset($_POST['resultados']) ? trim($_POST['resultados']) : '',
    'idUsuarioMedico' => $_SESSION['id_usuario'] ?? null,
];

if (!$datos['idUsuarioMedico']) {
    $objMensaje->mensajeSistemaShow("No se pudo identificar al médico", $urlRetorno, "error");
    exit();
}

try {
    // PATRÓN FACTORY METHOD: Creación del Command
    // Atributo: $comando (Instancia de Comando Concreto)
    $comando = ComandoEdicionFactory::crearComando('actualizar', $objControl, $datos);
    
    // PATRÓN COMMAND: Ejecución
    // Método: ejecutar
    $comando->ejecutar(); 

} catch (Exception $e) {
    // Si el Factory falla, es un error interno del sistema
    $objMensaje->mensajeSistemaShow("Error interno del sistema: " . $e->getMessage(), $urlRetorno, "error");
}
?>