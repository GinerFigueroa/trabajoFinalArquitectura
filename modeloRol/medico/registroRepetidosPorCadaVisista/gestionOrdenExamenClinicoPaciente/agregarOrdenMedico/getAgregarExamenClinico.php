<?php
session_start();

include_once('./controlAgregarExamenClinico.php');
include_once('../../../../../shared/mensajeSistema.php');

// Verificar que es una petición POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Método no permitido.", "../indexOrdenExamenClinico.php", "error");
    exit();
}

// Validar campos requeridos
$camposRequeridos = ['historia_clinica_id', 'id_medico', 'fecha', 'tipo_examen'];
foreach ($camposRequeridos as $campo) {
    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow("Faltan campos obligatorios.", "./indexAgregarExamenClinico.php", "error");
        exit();
    }
}

// Recoger y limpiar datos
$historia_clinica_id = (int)$_POST['historia_clinica_id'];
$id_medico = (int)$_POST['id_medico'];
$fecha = $_POST['fecha'];
$tipo_examen = trim($_POST['tipo_examen']);
$indicaciones = isset($_POST['indicaciones']) ? trim($_POST['indicaciones']) : '';
$estado = isset($_POST['estado']) ? $_POST['estado'] : 'Pendiente';

// Validaciones básicas
if ($historia_clinica_id <= 0) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Historia clínica no válida.", "./indexAgregarExamenClinico.php", "error");
    exit();
}

if ($id_medico <= 0) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Médico no válido.", "./indexAgregarExamenClinico.php", "error");
    exit();
}

if (empty($tipo_examen)) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("El tipo de examen es obligatorio.", "./indexAgregarExamenClinico.php", "error");
    exit();
}

// Validar fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Formato de fecha no válido.", "./indexAgregarExamenClinico.php", "error");
    exit();
}

// Validar que la fecha no sea futura
if (strtotime($fecha) > time()) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("La fecha no puede ser futura.", "./indexAgregarExamenClinico.php", "error");
    exit();
}

// Llamar al controlador
$objControl = new controlAgregarExamenClinico();
$objControl->registrarOrden(
    $historia_clinica_id,
    $id_medico,
    $fecha,
    $tipo_examen,
    $indicaciones,
    $estado
);
?>