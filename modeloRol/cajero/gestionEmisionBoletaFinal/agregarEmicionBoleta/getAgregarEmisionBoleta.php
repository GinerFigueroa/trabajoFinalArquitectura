<?php
// Archivo: getAgregarEmisionBoleta.php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlAgregarEmisionBoleta.php');

$objControl = new controlAgregarEmisionBoleta();
$objMensaje = new mensajeSistema();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $objMensaje->mensajeSistemaShow("Acceso no autorizado.", "../../indexEmisionBoletaFinal.php", "error");
    exit();
}

// Recolección y saneamiento de datos
$idOrden = isset($_POST['id_orden']) ? (int)$_POST['id_orden'] : null;
$numeroBoleta = trim($_POST['numero_boleta'] ?? '');
$tipo = $_POST['tipo'] ?? null;
$montoTotal = $_POST['monto_total'] ?? null;
$metodoPago = $_POST['metodo_pago'] ?? null;

// La URL de error ahora lleva al formulario sin ID, para que el usuario pueda reintentar la selección.
$urlError = "./indexAgregarEmisionBoleta.php"; 

if (!$idOrden || empty($numeroBoleta) || empty($tipo) || empty($montoTotal) || empty($metodoPago)) {
    $objMensaje->mensajeSistemaShow("Faltan campos obligatorios, incluyendo la selección de la Orden.", $urlError, "error");
    exit();
}

$objControl->emitirBoleta($idOrden, $numeroBoleta, $tipo, $montoTotal, $metodoPago);
?>