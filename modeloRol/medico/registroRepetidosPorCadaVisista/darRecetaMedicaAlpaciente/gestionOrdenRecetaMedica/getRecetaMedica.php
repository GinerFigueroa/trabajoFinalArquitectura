<?php
// Directorio: /controlador/receta/getRecetaMedica.php

session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlRecetaMedica.php');

$objMensaje = new mensajeSistema();
// Atributo: $objControl (El Mediator)
$objControl = new controlRecetaMedica();

// Recolectar datos de la solicitud
$action = $_GET['action'] ?? null;
$idReceta = $_GET['id'] ?? null;

if ($action == 'eliminar' && $idReceta !== null) {
    $data = [
        'action' => $action,
        'idReceta' => $idReceta,
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // Método: `ejecutarComando`
    $objControl->ejecutarComando($action, $data);

} else {
    // Si la acción o el ID no son válidos
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexRecetaMedica.php", "systemOut", false);
}
?>