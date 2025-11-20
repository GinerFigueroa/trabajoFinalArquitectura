<?php
session_start();

include_once('./controlEditarExamenClinico.php');
include_once('../../../../../shared/mensajeSistema.php');

// Validar que es una petición POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Método no permitido", "../indexOrdenExamenClinico.php", "error");
    exit();
}

// Validar que se envió el ID de la orden
if (!isset($_POST['id_orden']) || !is_numeric($_POST['id_orden'])) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("ID de orden no válido", "../indexOrdenExamenClinico.php", "error");
    exit();
}

// Recoger y validar datos del formulario
$idOrden = (int)$_POST['id_orden'];
$historiaClinicaId = isset($_POST['historia_clinica_id']) ? (int)$_POST['historia_clinica_id'] : null;
$fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
$tipoExamen = isset($_POST['tipo_examen']) ? trim($_POST['tipo_examen']) : '';
$indicaciones = isset($_POST['indicaciones']) ? trim($_POST['indicaciones']) : '';
$estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
$resultados = isset($_POST['resultados']) ? trim($_POST['resultados']) : '';

// Obtener el ID del médico de la sesión (el médico que está editando)
$idUsuarioMedico = $_SESSION['id_usuario'] ?? null;

if (!$idUsuarioMedico) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("No se pudo identificar al médico", "../indexOrdenExamenClinico.php", "error");
    exit();
}

// Instanciar controlador y procesar la edición
$objControl = new controlEditarExamenClinico();
$objControl->editarOrdenExamen($idOrden, $historiaClinicaId, $idUsuarioMedico, $fecha, $tipoExamen, $indicaciones, $estado, $resultados);
?>