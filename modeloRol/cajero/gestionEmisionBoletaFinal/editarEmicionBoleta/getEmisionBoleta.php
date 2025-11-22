<?php

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarEmisionBoleta.php');

$objControl = new controlEditarEmisionBoleta();
$objMensaje = new mensajeSistema();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $objMensaje->mensajeSistemaShow("Acceso no autorizado.", "../indexEmisionBoletaFinal.php", "error");
    exit();
}

// Recolección y saneamiento de datos
$idBoleta = isset($_POST['id_boleta']) ? (int)$_POST['id_boleta'] : null;
$numeroBoleta = trim($_POST['numero_boleta'] ?? '');
$tipo = $_POST['tipo'] ?? null;
$montoTotal = $_POST['monto_total'] ?? null;
$metodoPago = $_POST['metodo_pago'] ?? null;

$urlError = $idBoleta ? "./indexEditarEmisionBoleta.php?id={$idBoleta}" : "../indexEmisionBoletaFinal.php";

if (!$idBoleta || empty($numeroBoleta) || empty($tipo) || empty($montoTotal) || empty($metodoPago)) {
    $objMensaje->mensajeSistemaShow("Faltan campos obligatorios o ID inválido.", $urlError, "error");
    exit();
}

$objControl->editarBoleta($idBoleta, $numeroBoleta, $tipo, $montoTotal, $metodoPago);
?>