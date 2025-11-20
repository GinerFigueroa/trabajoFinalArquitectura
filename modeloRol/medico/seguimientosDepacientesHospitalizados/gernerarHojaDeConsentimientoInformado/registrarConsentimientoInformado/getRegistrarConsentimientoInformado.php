<?php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlRegistrarConsentimientoInformado.php');

$objMensaje = new mensajeSistema();
$objControl = new controlRegistrarConsentimientoInformado();

// --- 1. Manejo de Solicitudes AJAX ---
if (isset($_GET['action']) && $_GET['action'] == 'infoHC' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idHC = (int)$_GET['id'];
    
    header('Content-Type: application/json');
    $info = $objControl->obtenerInfoPacientePorHC($idHC);
    echo json_encode($info);
    exit();
}

// --- 2. Manejo de Registro (POST) ---
if (isset($_POST['btnRegistrar'])) {
    $historia_clinica_id = $_POST['historiaClinicaId'] ?? null;
    $id_paciente = $_POST['idPaciente'] ?? null;
    $dr_tratante_id = $_POST['drTratanteId'] ?? null;
    $diagnostico = $_POST['diagnostico'] ?? '';
    $tratamiento = $_POST['tratamiento'] ?? '';

    $objControl->registrarConsentimiento($historia_clinica_id, $id_paciente, $dr_tratante_id, $diagnostico, $tratamiento);
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado o acción no válida.', '../indexConsentimientoInformado.php', 'systemOut', false);
}
?>