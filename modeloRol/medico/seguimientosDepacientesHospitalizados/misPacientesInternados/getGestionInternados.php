<?php
session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlGestionInternados.php');

$objMensaje = new mensajeSistema();
$objControl = new controlGestionInternados();

if (isset($_GET['action']) && $_GET['action'] == 'alta' && isset($_GET['id'])) {
    $idInternado = $_GET['id'];
    
    if (!is_numeric($idInternado)) {
        $objMensaje->mensajeSistemaShow("ID de internado no válido.", "./indexGestionInternados.php", "error");
    } else {
        $objControl->darAltaInternado($idInternado);
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexGestionInternados.php", "error");
}
?>