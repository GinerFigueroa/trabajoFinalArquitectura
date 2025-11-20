<?php
session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlAgregarHistorialAnemia.php');

$objMensaje = new mensajeSistema();

if (isset($_POST['btnAgregar'])) {
    $objControl = new controlAgregarHistorialAnemia();
    $objControl->agregarHistorial($_POST);
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado.', '../indexHistorialAnemia.php', 'error');
}
?>