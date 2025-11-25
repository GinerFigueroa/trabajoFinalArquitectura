<?php
// getGestionInternados.php
session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlGestionInternados.php'); // Incluye el Mediator/Control y todos los patrones

$objMensaje = new mensajeSistema();
$objControl = new controlGestionInternados(); // Instancia el Mediator

// Verificación de la acción y parámetros necesarios
if (isset($_GET['action']) && $_GET['action'] == 'alta' && isset($_GET['id'])) {
    $idInternado = $_GET['id'];
    
    // El control/mediator maneja la validación de is_numeric, pero hacemos una verificación básica.
    if (!is_numeric($idInternado)) {
        $objMensaje->mensajeSistemaShow("ID de internado no válido.", "./indexGestionInternados.php", "error");
    } else {
        // La solicitud es delegada al Mediator
        $objControl->darAltaInternado($idInternado);
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexGestionInternados.php", "error");
}
?>