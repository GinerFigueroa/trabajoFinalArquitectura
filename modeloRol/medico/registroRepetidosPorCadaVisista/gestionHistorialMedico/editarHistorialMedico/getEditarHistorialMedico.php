<?php

session_start();

include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlEditarHistorialMedico.php');

// Verificar que es una petición POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Método no permitido.", "../indexHistorialMedico.php", "error");
    exit();
}

// Validar que el ID del registro esté presente
if (!isset($_POST['registro_medico_id']) || empty($_POST['registro_medico_id'])) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("ID de registro no proporcionado.", "../indexHistorialMedico.php", "error");
    exit();
}

// Validar campos requeridos
if (!isset($_POST['motivo_consulta'])) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("Faltan campos obligatorios.", "../indexHistorialMedico.php", "error");
    exit();
}

// Recoger y limpiar datos
$registro_medico_id = (int)$_POST['registro_medico_id'];
$motivo_consulta = trim($_POST['motivo_consulta']);
$enfermedad_actual = isset($_POST['enfermedad_actual']) ? trim($_POST['enfermedad_actual']) : '';
$tiempo_enfermedad = isset($_POST['tiempo_enfermedad']) ? trim($_POST['tiempo_enfermedad']) : '';
$signos_sintomas = isset($_POST['signos_sintomas']) ? trim($_POST['signos_sintomas']) : '';
$riesgos = isset($_POST['riesgos']) ? trim($_POST['riesgos']) : '';
$motivo_ultima_visita = isset($_POST['motivo_ultima_visita']) ? trim($_POST['motivo_ultima_visita']) : '';
$ultima_visita_medica = isset($_POST['ultima_visita_medica']) && !empty($_POST['ultima_visita_medica']) ? $_POST['ultima_visita_medica'] : null;

// Validar ID de registro
if ($registro_medico_id <= 0) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("ID de registro no válido.", "../indexHistorialMedico.php", "error");
    exit();
}

// Validar motivo de consulta
if (empty($motivo_consulta)) {
    $objMensaje = new mensajeSistema();
    $objMensaje->mensajeSistemaShow("El motivo de consulta es obligatorio.", "./indexEditarHistorialMedico.php?reg_id=" . $registro_medico_id, "error");
    exit();
}

// Llamar al controlador
$objControl = new controlEditarHistorialPaciente();
$objControl->editarRegistro(
    $registro_medico_id,
    $motivo_consulta,
    $enfermedad_actual,
    $tiempo_enfermedad,
    $signos_sintomas,
    $riesgos,
    $motivo_ultima_visita,
    $ultima_visita_medica
);
?>