<?php
// Archivo: getAgregarFacturaInternado.php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlAgregarFacturaInternado.php');

$objControl = new controlAgregarFacturaInternado();
$objMensaje = new mensajeSistema();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $objMensaje->mensajeSistemaShow("Acceso no autorizado.", "../../indexFacturacionInternado.php", "error");
    exit();
}

// Recolección y saneamiento de datos
$idInternado = isset($_POST['id_internado']) ? (int)$_POST['id_internado'] : null;
$fechaEmision = $_POST['fecha_emision'] ?? null;
$diasInternado = isset($_POST['dias_internado']) ? (int)$_POST['dias_internado'] : null;
$costoHabitacion = $_POST['costo_habitacion'] ?? 0.00;
$costoTratamientos = $_POST['costo_tratamientos'] ?? 0.00;
$costoMedicamentos = $_POST['costo_medicamentos'] ?? 0.00;
$costoOtros = $_POST['costo_otros'] ?? 0.00;
$total = $_POST['total'] ?? null;
$estado = $_POST['estado'] ?? null;

$urlError = "./indexAgregarFacturaInternado.php";

if (!$idInternado || empty($fechaEmision) || empty($diasInternado) || empty($total) || empty($estado)) {
    $objMensaje->mensajeSistemaShow("Faltan campos obligatorios.", $urlError, "error");
    exit();
}

$objControl->registrarFacturaInternado(
    $idInternado, $fechaEmision, $diasInternado, $costoHabitacion, 
    $costoTratamientos, $costoMedicamentos, $costoOtros, $total, $estado
);
?>