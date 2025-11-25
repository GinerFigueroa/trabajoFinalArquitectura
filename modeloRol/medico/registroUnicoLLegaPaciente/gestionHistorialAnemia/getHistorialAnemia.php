<?php
// Directorio: /controlador/historialAnemia/getHistorialAnemia.php

session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlHistorialAnemia.php');

$objMensaje = new mensajeSistema();
// Atributo: `$objControl` (El Mediator)
$objControl = new controlHistorialAnemia();

// Manejar diferentes acciones
if (isset($_GET['action'])) {
    // Atributo: `$action`
    $action = $_GET['action'];
    
    // Preparar datos
    $data = [];
    $data['id'] = $_GET['id'] ?? null;
    $data['termino'] = $_GET['termino'] ?? null;
    
    // Invocar al Mediator
    // Método: `ejecutarComando`
    $objControl->ejecutarComando($action, $data);

} else {
    // Si no hay acción, acceso denegado
    $objMensaje->mensajeSistemaShow("Acceso denegado. Acción requerida.", "./indexHistorialAnemia.php", "error");
}
?>