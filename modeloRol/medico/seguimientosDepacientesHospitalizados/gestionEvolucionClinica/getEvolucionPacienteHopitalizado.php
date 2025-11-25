<?php
// Directorio: /controlador/seguimiento/getEvolucionPacienteHopitalizado.php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEvolucionPacienteHospitalizado.php');

$objControl = new controlEvolucionPacienteHospitalizado(); // Mediator
$objMensaje = new mensajeSistema(); // Dependency (Mensajería)

// Manejo de la acción de ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    // Atributo: `$idSeguimiento` (Dato a procesar)
    $idSeguimiento = $_GET['id'];
    
    if (!is_numeric($idSeguimiento)) {
        $objMensaje->mensajeSistemaShow("ID de seguimiento no válido.", "./indexEvolucionClinicaPacienteHospitalizado.php", "error"); 
    } else {
        // MEDIATOR: Invoca el método coordinador
        // Método: `ejecutarComando`
        $objControl->ejecutarComando('eliminar', ['id' => $idSeguimiento]);
    }
} else {
    // Si no hay acción válida, redirige al formulario principal
    header("Location: ./indexEvolucionClinicaPacienteHospitalizado.php");
    exit();
}
?>