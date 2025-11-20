<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\administrador\gestionUsuario\getGestionUsuario.php
session_start();

include_once('../../../shared/mensajeSistema.php');
include_once('./controlGestionUsuario.php');

$objControl = new controlGestionUsuario();
$objMensaje = new mensajeSistema();

// Emulación del CHAIN OF RESPONSIBILITY (Validación de existencia de parámetros)
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $idUsuario = $_GET['id'];
    
    // El controlador ejecuta la validación (CHAIN) y la acción (COMMAND)
    $objControl->eliminarUsuario($idUsuario);
    
} else {
    // Falla el CHAIN de parámetros iniciales
    $objMensaje->mensajeSistemaShow("Acción no reconocida o parámetros incompletos (CHAIN FAILED)", "./indexGestionUsuario.php", "error");
}
?>