<?php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlRecetaMedica.php');

$objMensaje = new mensajeSistema();
$objControl = new controlRecetaMedica();

if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idReceta = $_GET['id'];
    
    if (!is_numeric($idReceta)) {
        $objMensaje->mensajeSistemaShow("ID de receta no válido.", "./indexRecetaMedica.php", "systemOut", false);
    } else {
        $objControl->eliminarReceta($idReceta);
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexRecetaMedica.php", "systemOut", false);
}
?>