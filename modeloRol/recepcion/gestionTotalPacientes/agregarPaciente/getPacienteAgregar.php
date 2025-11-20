<?php
// C:\...\gestionTotalPacientes\agregarPaciente\getPacienteAgregar.php
session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlPacienteAgregar.php');

$objControl = new controlPacienteAgregar();
$objMensaje = new mensajeSistema();

/**
 * Emulación del patrón BUILDER: Construye el array de datos
 * Se asegura que los datos se lean del POST y tengan formato inicial.
 */
function buildPacienteDataFromPost() {
    return [
        'idUsuario' => (int)($_POST['regUsuario'] ?? 0),
        'dni' => trim($_POST['regDNI'] ?? ''),
        'fechaNacimiento' => trim($_POST['regFechaNacimiento'] ?? ''),
        'lugarNacimiento' => trim($_POST['regLugarNacimiento'] ?? ''),
        'ocupacion' => trim($_POST['regOcupacion'] ?? ''),
        'domicilio' => trim($_POST['regDomicilio'] ?? ''),
        'distrito' => trim($_POST['regDistrito'] ?? ''),
        'edad' => (int)($_POST['regEdad'] ?? 0),
        'sexo' => trim($_POST['regSexo'] ?? ''),
        'estadoCivil' => trim($_POST['regEstadoCivil'] ?? ''),
        'nombreApoderado' => trim($_POST['regNombreApoderado'] ?? ''),
        'apellidoPaternoApoderado' => trim($_POST['regApellidoPaternoApoderado'] ?? ''),
        'apellidoMaternoApoderado' => trim($_POST['regApellidoMaternoApoderado'] ?? ''),
        'parentescoApoderado' => trim($_POST['regParentescoApoderado'] ?? '')
    ];
}

// 1. Validaciones Básicas (Previas al Builder y Command)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'registrar') {
    $objMensaje->mensajeSistemaShow("Acceso no autorizado o acción no válida. (POST Check)", './indexPacienteAgregar.php', 'error');
    exit();
}

// 2. Uso del BUILDER
$data = buildPacienteDataFromPost();

// 3. Validación de campos obligatorios (CHAIN simplificado/Check de Datos Básicos)
// NOTA: Esta validación es rudimentaria, la validación completa la hace el CHAIN en el controlador.
if (empty($data['idUsuario']) || empty($data['dni']) || empty($data['fechaNacimiento']) || empty($data['domicilio']) || empty($data['ocupacion'])) {
    $objMensaje->mensajeSistemaShow('Faltan campos obligatorios para el registro del paciente.', './indexPacienteAgregar.php', 'systemOut', false);
    exit();
}

// 4. Ejecución del COMMAND (Delegación al Controlador/Mediator)
// Corregido: Pasar el array $data completo al controlador.
$objControl->registrarPaciente($data); 
?>