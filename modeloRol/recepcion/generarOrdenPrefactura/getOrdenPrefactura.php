<?php

session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlOrdenPrefactura.php');

$objMensaje = new mensajeSistema();
$objControl = new controlOrdenPrefactura();

if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idOrden = $_GET['id'];
    
    if (!is_numeric($idOrden)) {
        $objMensaje->mensajeSistemaShow("ID de orden no válido.", "./indexOdenPrefactura.php", "systemOut", false);
    } else {
        $objControl->eliminarOrden($idOrden);
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexOdenPrefactura.php", "systemOut", false);
}
?>