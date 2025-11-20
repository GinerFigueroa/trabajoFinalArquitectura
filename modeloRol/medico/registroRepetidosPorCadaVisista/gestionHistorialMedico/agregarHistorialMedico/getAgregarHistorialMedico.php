<?php

session_start();

include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlAgregarHistorialMedico.php');

// Verificar que es una petición POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Método no permitido.", "../indexHistorialClinico.php", "error");
    exit();
}

// Validar campos requeridos
if (!isset($_POST['historia_clinica_id']) || !isset($_POST['motivo_consulta'])) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Faltan campos obligatorios.", "../agregarHistorialPaciente/indexAgregarHistorialPaciente.php", "error");
    exit();
}

// Recoger y limpiar datos
$historia_clinica_id = (int)$_POST['historia_clinica_id'];
$motivo_consulta = trim($_POST['motivo_consulta']);
$enfermedad_actual = isset($_POST['enfermedad_actual']) ? trim($_POST['enfermedad_actual']) : '';
$tiempo_enfermedad = isset($_POST['tiempo_enfermedad']) ? trim($_POST['tiempo_enfermedad']) : '';
$signos_sintomas = isset($_POST['signos_sintomas']) ? trim($_POST['signos_sintomas']) : '';
$riesgos = isset($_POST['riesgos']) ? trim($_POST['riesgos']) : '';
$motivo_ultima_visita = isset($_POST['motivo_ultima_visita']) ? trim($_POST['motivo_ultima_visita']) : '';
$ultima_visita_medica = isset($_POST['ultima_visita_medica']) && !empty($_POST['ultima_visita_medica']) ? $_POST['ultima_visita_medica'] : null;

// Validar ID de historia clínica
if ($historia_clinica_id <= 0) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Historia clínica no válida.", "../agregarHistorialPaciente/indexAgregarHistorialPaciente.php", "error");
    exit();
}

// Validar motivo de consulta
if (empty($motivo_consulta)) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("El motivo de consulta es obligatorio.", "../agregarHistorialPaciente/indexAgregarHistorialPaciente.php", "error");
    exit();
}

// Validar fecha si se proporcionó
if ($ultima_visita_medica) {
    if (strtotime($ultima_visita_medica) > time()) {
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow("La fecha de última visita médica no puede ser futura.", "../agregarHistorialPaciente/indexAgregarHistorialPaciente.php", "error");
        exit();
    }
}

// Llamar al controlador
$objControl = new controlAgregarHistorialPaciente();
$objControl->registrarRegistro(
    $historia_clinica_id,
    $motivo_consulta,
    $enfermedad_actual,
    $tiempo_enfermedad,
    $signos_sintomas,
    $riesgos,
    $motivo_ultima_visita,
    $ultima_visita_medica
);
?>