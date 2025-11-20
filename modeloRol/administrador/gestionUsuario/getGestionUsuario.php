<?php
session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlGestionUsuario.php');

$objControl = new controlGestionUsuario();
$objMensaje = new mensajeSistema();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $idUsuario = (int)$_GET['id'];
    $action = $_GET['action'];
    
    switch ($action) {
        case 'eliminar':
            $objControl->eliminarUsuario($idUsuario);
            break;
        case 'desactivar':
            $objControl->desactivarUsuario($idUsuario);
            break;
        case 'reactivar': // NUEVO CASO
            $objControl->reactivarUsuario($idUsuario);
            break;
        default:
            $objMensaje->mensajeSistemaShow("Acción no reconocida", "./indexGestionUsuario.php", "error");
    }
} else {
    $objMensaje->mensajeSistemaShow("Parámetros incompletos", "./indexGestionUsuario.php", "error");
}