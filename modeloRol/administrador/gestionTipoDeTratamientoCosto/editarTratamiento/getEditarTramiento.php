<?php
// C:\...\editarTratamiento\getEditarTratamiento.php
session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEditarTratamiento.php');

$objMensaje = new mensajeSistema();
$objControl = new controlEditarTratamiento();

/**
 * PATRÓN: BUILDER (Construye el DTO/Array a partir de los datos POST)
 * Nota: Los nombres de las claves deben coincidir con los campos esperados en el DAO/Controlador.
 */
function buildTratamientoEditDataFromPost() {
    return [
        'idTratamiento' => (int)($_POST['idTratamiento'] ?? 0),
        'nombre' => trim($_POST['editNombre'] ?? ''),
        'idEspecialidad' => (int)($_POST['editEspecialidad'] ?? 0),
        'descripcion' => trim($_POST['editDescripcion'] ?? ''),
        'duracion' => (int)($_POST['editDuracion'] ?? 0), // duracion_estimada en DB
        'costo' => floatval($_POST['editCosto'] ?? 0.0),
        'requisitos' => trim($_POST['editRequisitos'] ?? ''),
        'activo' => trim($_POST['editActivo'] ?? '0')
    ];
}

// Validación de la acción POST (Simulación de CHAIN OF RESPONSIBILITY)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'editar') {
    $objMensaje->mensajeSistemaShow('Acceso denegado o acción no válida.', '../indexTipoTratamiento.php', 'error');
    exit();
}

// 1. Uso del BUILDER
$data = buildTratamientoEditDataFromPost();

// 2. Validación de datos mínimos
$idRedireccion = $data['idTratamiento'] > 0 ? $data['idTratamiento'] : 0; 

if ($data['idTratamiento'] <= 0 || empty($data['nombre']) || $data['idEspecialidad'] <= 0 || $data['costo'] < 0) {
    $objMensaje->mensajeSistemaShow('Faltan campos obligatorios o el ID es inválido.', './indexEditarTratamiento.php?id=' . $idRedireccion, 'systemOut', false);
    exit();
}

// 3. Ejecución del COMMAND (Delegación al Controlador/Mediator)
$objControl->editarTratamiento($data);
?>