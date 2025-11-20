<?php
session_start();

include_once('./controlAgregarHistorialPaciente.php');
include_once('../../../../shared/mensajeSistema.php');

$objControl = new controlAgregarHistorialPaciente();
$objMensaje = new mensajeSistema();

// --- Bloque de verificación de Rol ELIMINADO ---

// Verificar solo que haya un usuario logueado
if (!isset($_SESSION['id_usuario'])) {
    $objMensaje->mensajeSistemaShow("Debe iniciar sesión para realizar esta acción.", "../../../vista/login.php", "error"); 
    exit();
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objControl->agregarHistoria($_POST);
} else {
    header("Location: ./indexAgregarHistorialPaciente.php");
    exit();
}
?>