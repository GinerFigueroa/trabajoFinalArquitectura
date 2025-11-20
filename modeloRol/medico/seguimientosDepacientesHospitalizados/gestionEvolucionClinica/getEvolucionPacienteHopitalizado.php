<?php
session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEvolucionPacienteHospitalizado.php');

$objControl = new controlEvolucionPacienteHospitalizado();
$objMensaje = new mensajeSistema();

// Manejo de la acción de ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idSeguimiento = $_GET['id'];
    
    if (!is_numeric($idSeguimiento)) {
        $objMensaje->mensajeSistemaShow("ID de seguimiento no válido.", "./indexEvolucionClinicaPacienteHospitalizado.php", "error"); 
    } else {
        $objControl->eliminarSeguimiento($idSeguimiento);
    }
} else {
    // Si no hay acción válida, redirige al formulario principal
    header("Location: ./indexEvolucionClinicaPacienteHospitalizado.php");
    exit();
}
?>