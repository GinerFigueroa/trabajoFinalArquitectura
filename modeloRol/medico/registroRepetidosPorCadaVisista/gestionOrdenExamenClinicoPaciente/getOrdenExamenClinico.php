<?php
session_start();

include_once('../../../../shared/mensajeSistema.php');
include_once('./controlExamenClinico.php');

$objControl = new controlExamenClinico();
$objMensaje = new mensajeSistema();

// Manejo de la acción de ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id_orden'])) {
    $idOrden = (int)$_GET['id_orden'];
    
    if (!is_numeric($idOrden) || $idOrden <= 0) {
        $objMensaje->mensajeSistemaShow("ID de orden no válido.", "./indexOrdenExamenClinico.php", "error");
    } else {
        $objControl->eliminarOrden($idOrden);
    }
} else {
    // Si no hay acción válida, redirige al formulario principal
    header("Location: ./indexOrdenExamenClinico.php");
    exit();
}
?>