<?php
// C:\...\agregarTratamiento\getAgregarTratamiento.php
session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlAgregarTratamiento.php');

$objMensaje = new mensajeSistema();
$objControl = new controlAgregarTratamiento();

/**
 * PATRÓN: BUILDER (Construye el DTO/Array a partir de los datos POST)
 */
function buildTratamientoRegisterDataFromPost() {
    return [
        // 'activo' se establece a 1 (Activo) por defecto para el registro
        'activo' => 1, 
        'nombre' => trim($_POST['regNombre'] ?? ''),
        'idEspecialidad' => (int)($_POST['regEspecialidad'] ?? 0),
        'descripcion' => trim($_POST['regDescripcion'] ?? ''),
        'duracion' => (int)($_POST['regDuracion'] ?? 0), // duracion_estimada
        'costo' => floatval($_POST['regCosto'] ?? 0.0),
        'requisitos' => trim($_POST['regRequisitos'] ?? '')
    ];
}

// Validación de la acción POST (Chain simplificada)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'registrar') {
    $objMensaje->mensajeSistemaShow('Acceso denegado o acción no válida.', '../indexTipoTratamiento.php', 'error');
    exit();
}

// 1. Uso del BUILDER
$data = buildTratamientoRegisterDataFromPost();

// 2. Validación de datos mínimos (Chain simplificada)
if (empty($data['nombre']) || $data['idEspecialidad'] <= 0 || $data['duracion'] <= 0 || $data['costo'] < 0) {
    $objMensaje->mensajeSistemaShow('Faltan campos obligatorios o los valores son inválidos.', './indexAgregarTratamiento.php', 'systemOut', false);
    exit();
}

// 3. Ejecución del COMMAND (Delegación al Controlador/Mediator)
$objControl->registrarTratamiento($data);
?>