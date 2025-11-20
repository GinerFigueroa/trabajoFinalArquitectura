<?php

session_start();

include_once('./controlEditarPacienteHospitalizado.php');
$objControl = new controlEditarPacienteHospitalizado();
include_once('../../../../../shared/mensajeSistema.php');
$objMensaje = new mensajeSistema();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validar ID de seguimiento
    $idSeguimiento = isset($_POST['idSeguimiento']) ? (int)$_POST['idSeguimiento'] : null;

    if (empty($idSeguimiento)) {
        $objMensaje->mensajeSistemaShow("ID de registro de seguimiento faltante o no válido.", "../indexEvolucionClinicaPacienteHospitalizado.php", "error");
        exit();
    }
    
    // Recoger y limpiar datos del formulario
    $idInternado = isset($_POST['idInternado']) ? (int)$_POST['idInternado'] : null;
    $idMedico = isset($_POST['idMedico']) ? (int)$_POST['idMedico'] : null;
    $idEnfermera = isset($_POST['idEnfermera']) && !empty($_POST['idEnfermera']) ? (int)$_POST['idEnfermera'] : null;
    $evolucion = isset($_POST['evolucion']) ? trim($_POST['evolucion']) : '';
    $tratamiento = isset($_POST['tratamiento']) ? trim($_POST['tratamiento']) : '';

    // Llamar al controlador para editar
    $objControl->editarEvolucion($idSeguimiento, $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento);
    
} else {
    // Si no es POST, redirigir al formulario principal de gestión
    header("Location: ../indexEvolucionClinicaPacienteHospitalizado.php");
    exit();
}
?>