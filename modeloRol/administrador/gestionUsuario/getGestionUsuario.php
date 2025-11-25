<?php
// Directorio: /controlador/gestionUsuario/getGestionUsuario.php

session_start();

include_once('../../../shared/mensajeSistema.php');
// Incluye el Mediator/Controlador refactorizado
include_once('./controlGestionUsuario.php');

$objMensaje = new mensajeSistema();
$objControl = new controlGestionUsuario();

// Se espera una acción y un ID por GET
if (isset($_GET['action']) && isset($_GET['id'])) {
    $idUsuario = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // El Mediator se encarga de validar el ID, crear el Command y ejecutarlo.
    $objControl->ejecutarAccionUsuario($action, $idUsuario);

} else {
    // Si faltan parámetros, se muestra un mensaje de error.
    $objMensaje->mensajeSistemaShow("Parámetros incompletos para la acción.", "./indexGestionUsuario.php", "error");
}
?>