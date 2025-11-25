<?php

session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlRegistrarConsentimientoInformado.php');

$objMensaje = new mensajeSistema();
// Atributo: `$objControl` (El Mediator)
$objControl = new controlRegistrarConsentimientoInformado();

// --- 1. Manejo de Solicitudes AJAX (Recuperar Info HC) ---
$action = $_GET['action'] ?? null;
$id = $_GET['id'] ?? null;

if ($action == 'infoHC' && is_numeric($id)) {
    $idHC = (int)$id;
    
    header('Content-Type: application/json');
    // Método: `obtenerInfoPacientePorHC` (Maneja la lógica de obtener datos auxiliares)
    $info = $objControl->obtenerInfoPacientePorHC($idHC);
    echo json_encode($info);
    exit();
}

// --- 2. Manejo de Registro (POST - Registro Command) ---
if (isset($_POST['btnRegistrar'])) {
    // Recoger y limpiar datos
    $data = [
        // Atributo: `historiaClinicaId`
        'historiaClinicaId' => $_POST['historiaClinicaId'] ?? null,
        // Atributo: `idPaciente`
        'idPaciente' => $_POST['idPaciente'] ?? null,
        // Atributo: `drTratanteId`
        'drTratanteId' => $_POST['drTratanteId'] ?? null,
        // Atributo: `diagnostico`
        'diagnostico' => $_POST['diagnostico'] ?? '',
        // Atributo: `tratamiento`
        'tratamiento' => $_POST['tratamiento'] ?? '',
    ];

    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // Método: `ejecutarComando`
    $objControl->ejecutarComando('registrar', $data);
    
} else {
    $objMensaje->mensajeSistemaShow('Acceso denegado o acción no válida.', '../indexConsentimientoInformado.php', 'systemOut', false);
}
?>