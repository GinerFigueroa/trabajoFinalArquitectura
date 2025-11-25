<?php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlAgregarTratamiento.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (Representa la instancia del Mediator)
$objControl = new controlAgregarTratamiento();

/**
 * PATRÓN: BUILDER (Construye el array de datos DTO a partir de POST)
 * Método: `buildTratamientoRegisterDataFromPost`
 */
function buildTratamientoRegisterDataFromPost() {
    return [
        // 'activo' se establece a 1 (Activo) por defecto para el registro
        'activo' => 1, 
        // Atributo: nombre
        'nombre' => trim($_POST['regNombre'] ?? ''),
        // Atributo: idEspecialidad
        'idEspecialidad' => (int)($_POST['regEspecialidad'] ?? 0),
        // Atributo: descripcion
        'descripcion' => trim($_POST['regDescripcion'] ?? ''),
        // Atributo: duracion (duracion_estimada)
        'duracion' => (int)($_POST['regDuracion'] ?? 0),
        // Atributo: costo
        'costo' => floatval($_POST['regCosto'] ?? 0.0),
        // Atributo: requisitos
        'requisitos' => trim($_POST['regRequisitos'] ?? '')
    ];
}

// Validación de la acción POST (Invoker)
$action = $_POST['action'] ?? null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $action !== 'registrar') {
    $objMensaje->mensajeSistemaShow('Acceso denegado o acción no válida.', '../indexTipoTratamiento.php', 'error');
    exit();
}

// 1. Uso del BUILDER
$data = buildTratamientoRegisterDataFromPost();

// 2. Validación de datos mínimos (Simulación de CHAIN OF RESPONSIBILITY del Invoker)
if (empty($data['nombre']) || $data['idEspecialidad'] <= 0 || $data['duracion'] <= 0 || $data['costo'] < 0) {
    $objMensaje->mensajeSistemaShow('Faltan campos obligatorios o los valores son inválidos.', './indexAgregarTratamiento.php', 'systemOut', false);
    exit();
}

// 3. MEDIATOR: Invoca el método coordinador.
// Atributo: Método `ejecutarComando`
$objControl->ejecutarComando('registrar', $data);
?>