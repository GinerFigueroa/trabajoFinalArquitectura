<?php
session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlConsentimientoInformado.php');

$objMensaje = new mensajeSistema();
$objControl = new controlConsentimientoInformado();

if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    if (!is_numeric($id)) {
        $objMensaje->mensajeSistemaShow("ID de consentimiento no válido.", "./indexConsentimientoInformado.php", "systemOut", false);
    } else {
        $objControl->eliminarConsentimiento($id);
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexConsentimientoInformado.php", "systemOut", false);
}
?>