<?php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarTratamiento.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (Representa la instancia del Mediator)
$objControl = new controlEditarTratamiento();

/**
 * PATRÓN: BUILDER (Construye el array de datos DTO)
 * Método: `buildTratamientoEditDataFromPost`
 */
function buildTratamientoEditDataFromPost() {
    return [
        // Atributo: idTratamiento
        'idTratamiento' => (int)($_POST['idTratamiento'] ?? 0),
        // Atributo: nombre
        'nombre' => trim($_POST['editNombre'] ?? ''),
        // Atributo: idEspecialidad
        'idEspecialidad' => (int)($_POST['editEspecialidad'] ?? 0),
        // Atributo: descripcion
        'descripcion' => trim($_POST['editDescripcion'] ?? ''),
        // Atributo: duracion (duracion_estimada)
        'duracion' => (int)($_POST['editDuracion'] ?? 0),
        // Atributo: costo
        'costo' => floatval($_POST['editCosto'] ?? 0.0),
        // Atributo: requisitos
        'requisitos' => trim($_POST['editRequisitos'] ?? ''),
        // Atributo: activo
        'activo' => trim($_POST['editActivo'] ?? '0')
    ];
}

// Validación de la acción POST (Invoker)
$action = $_POST['action'] ?? null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $action !== 'editar') {
    $objMensaje->mensajeSistemaShow('Acceso denegado o acción no válida.', '../indexTipoTratamiento.php', 'error');
    exit();
}

// 1. Uso del BUILDER
$data = buildTratamientoEditDataFromPost();

// Redirección de fallback si el ID no es válido
$idRedireccion = $data['idTratamiento'] > 0 ? $data['idTratamiento'] : 0; 

// Validación rápida de datos mínimos (Simulación de CHAIN OF RESPONSIBILITY del Invoker)
if ($data['idTratamiento'] <= 0 || empty($data['nombre']) || $data['idEspecialidad'] <= 0 || $data['costo'] < 0) {
    $objMensaje->mensajeSistemaShow('Faltan campos obligatorios o el ID es inválido.', './indexEditarTratamiento.php?id=' . $idRedireccion, 'systemOut', false);
    exit();
}

// 2. MEDIATOR: Invoca el método coordinador.
// Atributo: Método `ejecutarComando`
$objControl->ejecutarComando('editar', $data);
?>