<?php
// FILE: getOrdenPrefactura.php
session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlOrdenPrefactura.php'); // Ahora contiene al Mediator y todas las clases de patrones

$objMensaje = new mensajeSistema();
$objControl = new controlOrdenPrefactura(); // PATRÓN: MEDIATOR

// 1. Recolección de la solicitud
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idOrden = $_GET['id'];
    $accion = $_GET['action'];
    
    if (!is_numeric($idOrden)) {
        $objMensaje->mensajeSistemaShow("ID de orden no válido.", "./indexOdenPrefactura.php", "systemOut", false);
    } else {
        // 2. Preparación de los datos para el Command
        $data = [
            'idOrden' => (int)$idOrden,
            'ejecutadoPor' => $_SESSION['id_usuario'] ?? 1 // Datos de contexto para el Command
        ];

        // 3. El GET (Invocador) delega la acción al Mediator/Controlador
        $objControl->ejecutarAccion($accion, $data);
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexOdenPrefactura.php", "systemOut", false);
}
?>