<?php
session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlPaciente.php');

$objControl = new controlPaciente();
$objMensaje = new mensajeSistema();

// MODIFICADO: Ahora maneja 3 acciones diferentes
if (isset($_GET['action']) && isset($_GET['id'])) {
    $idPaciente = (int)$_GET['id'];
    $action = $_GET['action'];
    
    switch ($action) {
        case 'eliminar':
            $objControl->eliminarPaciente($idPaciente);
            break;
        case 'desactivar':
            $objControl->desactivarPaciente($idPaciente);
            break;
        case 'reactivar':
            $objControl->reactivarPaciente($idPaciente);
            break;
        default:
            $objMensaje->mensajeSistemaShow("Acción no reconocida", "./indexTotalPaciente.php", "error");
    }
} else {
    $objMensaje->mensajeSistemaShow("Parámetros incompletos", "./indexTotalPaciente.php", "error");
}
?>