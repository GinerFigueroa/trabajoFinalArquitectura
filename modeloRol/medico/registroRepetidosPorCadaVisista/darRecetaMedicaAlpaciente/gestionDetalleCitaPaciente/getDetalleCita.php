<?php
session_start();
include_once('../../../../../shared/mensajeSistema.php');
include_once('./controlDetalleCita.php');

$objMensaje = new mensajeSistema();
$objControl = new controlDetalleCita();

// Verificar que el usuario tenga sesión activa y sea médico
if (!isset($_SESSION['login']) || $_SESSION['rol_id'] != 2) {
    $objMensaje->mensajeSistemaShow(
        '❌ Acceso denegado. Solo el personal médico puede gestionar detalles de recetas.', 
        '../../../index.php', 
        'error'
    );
    exit();
}

// Manejo de acciones
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idDetalle = $_GET['id'];
    
    if (!is_numeric($idDetalle)) {
        $objMensaje->mensajeSistemaShow("ID de detalle no válido.", "./indexDetalleCita.php", "error");
    } else {
        $objControl->eliminarDetalle($idDetalle);
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado o acción no válida.", "./indexDetalleCita.php", "error");
}
?>