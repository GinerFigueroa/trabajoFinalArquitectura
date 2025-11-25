<?php

session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlTratamiento.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (Representa la instancia del Mediator)
$objControl = new controlTratamiento();

// Invoker/Contexto de la solicitud
$action = $_GET['action'] ?? null;
$idTratamiento = $_GET['id'] ?? null;

// Validación inicial de la solicitud (Invoker)
if ($action == 'eliminar' && $idTratamiento !== null) {
    $data = [
        'action' => $action,
        'idTratamiento' => $idTratamiento,
    ];
    
    // MEDIATOR: Invoca el método coordinador.
    // Atributo: Método `ejecutarComando`
    $objControl->ejecutarComando($action, $data);
    
} else {
    // Si la acción o el ID no son válidos
    $objMensaje->mensajeSistemaShow("Acción no reconocida o parámetros incompletos.", "./indexTipoTratamiento.php", "error");
}
?>