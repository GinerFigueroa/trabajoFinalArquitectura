<?php
// FILE: getAgregarOrdenPreFactura.php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlAgregarOdenPreFactura.php');

$objMensaje = new mensajeSistema();
$objControl = new controlAgregarOdenPreFactura(); // PATRÓN MEDIATOR

// --- 1. Manejo de Solicitudes AJAX para cargar Citas/Internados ---
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idPaciente = (int)$_GET['id'];
    
    // Configuración de respuesta JSON
    header('Content-Type: application/json');
    
    if ($_GET['action'] == 'citas') {
        $citas = $objControl->obtenerCitasPorPaciente($idPaciente);
        echo json_encode($citas);
        exit();
    } elseif ($_GET['action'] == 'internados') {
        $internados = $objControl->obtenerInternadosPorPaciente($idPaciente);
        echo json_encode($internados);
        exit();
    }
}

// --- 2. Manejo de Registro (POST) ---
if (isset($_POST['btnAgregar'])) {
    // 1. Recolección de datos
    $data = [
        'idPaciente' => $_POST['idPaciente'] ?? null,
        'idCita' => $_POST['idCita'] ?? null,
        'idInternado' => $_POST['idInternado'] ?? null,
        'concepto' => $_POST['concepto'] ?? '',
        'monto' => $_POST['monto'] ?? 0,
    ];

    // 2. El Invocador delega la tarea al Mediator
    $objControl->ejecutarRegistroAccion($data); // MÉTODO del Mediator
} else {
    // Si no es POST ni AJAX válido
    $objMensaje->mensajeSistemaShow('Acceso denegado o acción no válida.', '../indexOdenPrefactura.php', 'systemOut', false);
}
?>