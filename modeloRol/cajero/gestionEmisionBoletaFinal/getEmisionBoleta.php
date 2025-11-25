<?php

session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlEmisionBoleta.php'); // El Mediator

$objControl = new controlEmisionBoleta();
$objMensaje = new mensajeSistema();

$action = $_GET['action'] ?? null;
$idBoleta = isset($_GET['id']) ? (int)$_GET['id'] : null;

$urlRedireccion = "./indexEmisionBoletaFinal.php";

if ($action == 'eliminar' && $idBoleta) {
    
    $data = [
        'action' => $action,
        'idBoleta' => $idBoleta,
    ];
    
    // MEDIATOR: Invoca el método coordinador con la acción y los datos.
    // Reemplazamos la lógica directa por la llamada al nuevo método unificado
    $objControl->ejecutarComando($action, $data);

} else {
    // Si no es una acción válida o falta el ID, redirigir al listado
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", $urlRedireccion, "systemOut", false);
}
?>