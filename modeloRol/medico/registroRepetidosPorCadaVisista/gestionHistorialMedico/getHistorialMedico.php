<?php
// getHistorialClinico.php

session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlHistorialMedico.php');

$objControl = new controlHistorialClinico();
$objMensaje = new mensajeSistema();

// Manejo de la acción de ELIMINAR REGISTRO
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['reg_id'])) {
    
    $idRegistro = (int)$_GET['reg_id'];
    $objControl->eliminarRegistro($idRegistro);

} else {
    // Si no hay acción válida, redirige al listado principal
    header("Location: ./indexHistorialMedico.php");
    exit();
}
?>