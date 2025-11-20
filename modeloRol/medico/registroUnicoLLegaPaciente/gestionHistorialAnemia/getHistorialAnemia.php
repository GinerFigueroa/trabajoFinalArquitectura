<?php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('./controlHistorialAnemia.php');

$objMensaje = new mensajeSistema();
$objControl = new controlHistorialAnemia();

// Manejar diferentes acciones
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'eliminar':
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $idHistorial = $_GET['id'];
                $objControl->eliminarHistorial($idHistorial);
            } else {
                $objMensaje->mensajeSistemaShow("ID de historial no válido.", "./indexHistorialAnemia.php", "error");
            }
            break;
            
        case 'buscar':
            if (isset($_GET['termino'])) {
                $termino = $_GET['termino'];
                $objControl->buscarHistoriales($termino);
            } else {
                $objMensaje->mensajeSistemaShow("Término de búsqueda no proporcionado.", "./indexHistorialAnemia.php", "error");
            }
            break;
            
        default:
            $objMensaje->mensajeSistemaShow("Acción no válida.", "./indexHistorialAnemia.php", "error");
            break;
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado.", "./indexHistorialAnemia.php", "error");
}
?>