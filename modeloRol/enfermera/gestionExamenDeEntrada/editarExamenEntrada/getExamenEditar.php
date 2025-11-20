<?php

session_start();

include_once('./controlEditarPacienteHospitalizado.php');
$objControl = new controlEditarPacienteHospitalizado();
include_once('../../../../shared/mensajeSistema.php');
$objMensaje = new mensajeSistema();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validar ID de seguimiento (lo primero y más importante)
    $idSeguimiento = isset($_POST['idSeguimiento']) ? (int)$_POST['idSeguimiento'] : null;

    if (empty($idSeguimiento)) {
        $objMensaje->mensajeSistemaShow("Error fatal: ID de registro de seguimiento faltante o no válido.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
        exit();
    }
    
    // Recoger y limpiar datos del formulario
    $idInternado = isset($_POST['idInternado']) ? (int)$_POST['idInternado'] : null;
    $idMedico = isset($_POST['idMedico']) ? (int)$_POST['idMedico'] : null;
    // idEnfermera puede ser NULL
    $idEnfermera = isset($_POST['idEnfermera']) && !empty($_POST['idEnfermera']) ? (int)$_POST['idEnfermera'] : null;
    $evolucion = isset($_POST['evolucion']) ? $_POST['evolucion'] : '';
    $tratamiento = isset($_POST['tratamiento']) ? $_POST['tratamiento'] : '';

    // Llamar al controlador
    $objControl->editarEvolucion($idSeguimiento, $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento);
    
} else {
    // Si no es POST, redirigir al formulario principal de gestión
    header("Location: ../indexEvolucionClinicaPacienteHospitalizado.php");
    exit();
}
?>