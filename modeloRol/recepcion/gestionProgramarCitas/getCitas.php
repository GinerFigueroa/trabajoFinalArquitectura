<?php

session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlCitas.php');

$objControl = new controlCitas();
$objMensaje = new mensajeSistema();

if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idCita = $_GET['id'];
    
    if (!is_numeric($idCita)) {
        $objMensaje->mensajeSistemaShow("ID de cita no válido.", "./indexCita.php", "systemOut", false);
    } else {
        $objControl->eliminarCita($idCita);
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexCita.php", "systemOut", false);
}
?>