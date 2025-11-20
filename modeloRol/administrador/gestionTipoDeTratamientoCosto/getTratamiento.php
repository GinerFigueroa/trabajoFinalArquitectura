<?php
// C:\...\gestionTipoDeTratamientoCosto\getTratamiento.php
session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlTratamiento.php');

$objControl = new controlTratamiento();
$objMensaje = new mensajeSistema();

// CHAIN OF RESPONSIBILITY: Verifica que la acción 'eliminar' y el ID existan.
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idTratamiento = (int)$_GET['id'];
    
    // Ejecuta el COMMAND a través del Mediator
    $objControl->eliminarTratamiento($idTratamiento);
    
} else {
    $objMensaje->mensajeSistemaShow("Acción no reconocida o parámetros incompletos (CHAIN FAILED)", "./indexTipoTratamiento.php", "error");
}
?>