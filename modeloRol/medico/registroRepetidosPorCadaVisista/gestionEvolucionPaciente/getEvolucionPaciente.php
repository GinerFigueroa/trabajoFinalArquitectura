<?php
// getEvolucionPaciente.php
session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlEvolucionPaciente.php');

$objControl = new controlEvolucionPaciente();
$objMensaje = new mensajeSistema();

// Manejo de la acción de ELIMINAR EVOLUCIÓN
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['evo_id'])) {
    
    $idEvolucion = (int)$_GET['evo_id'];
    $objControl->eliminarEvolucion($idEvolucion);

} else {
    // Si no hay acción válida, redirige al listado principal
    header("Location: ./indexEvolucionPaciente.php");
    exit();
}
?>