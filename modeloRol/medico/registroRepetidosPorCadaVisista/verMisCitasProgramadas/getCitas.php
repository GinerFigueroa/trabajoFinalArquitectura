<?php
session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlCitas.php');

$objMensaje = new mensajeSistema();
$objControl = new controlCitas();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $idCita = $_GET['id'];
    $action = $_GET['action'];
    
    if (!is_numeric($idCita)) {
        $objMensaje->mensajeSistemaShow("ID de cita no válido.", "./indexCita.php", "error");
        exit;
    }

    switch ($action) {
        case 'confirmar':
            $objControl->confirmarCita($idCita);
            break;
        case 'cancelar':
            $objControl->cancelarCita($idCita);
            break;
        default:
            $objMensaje->mensajeSistemaShow("Acción no válida.", "./indexCita.php", "error");
            break;
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado.", "./indexCita.php", "error");
}
?>