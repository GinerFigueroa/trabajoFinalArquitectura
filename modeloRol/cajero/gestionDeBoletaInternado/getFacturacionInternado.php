<?php
// Archivo: getFacturacionInternado.php (Gateway para acciones de la lista principal)

session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlFacturacionInternado.php');

$objControl = new controlFacturacionInternado();
$objMensaje = new mensajeSistema();

$action = $_GET['action'] ?? null;
$idFactura = isset($_GET['id']) ? (int)$_GET['id'] : null;

$urlRedireccion = "./indexFacturacionInternado.php";

if ($action == 'eliminar' && $idFactura) {
    if (!is_numeric($idFactura)) {
        $objMensaje->mensajeSistemaShow("ID de factura no válido.", $urlRedireccion, "error");
    } else {
        $objControl->eliminarFacturaInternado($idFactura);
    }
} else {
    header("Location: {$urlRedireccion}");
    exit();
}
?>