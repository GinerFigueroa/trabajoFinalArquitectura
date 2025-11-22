<?php
// C:\...\editarHistorialPaciente\getEditarHistorialPaciente.php
session_start();

include_once('./controlEditarHistorialPaciente.php');
include_once('../../../../shared/mensajeSistema.php');

$objControl = new controlEditarHistorialPaciente();
$objMensaje = new mensajeSistema();

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    $objMensaje->mensajeSistemaShow("Debe iniciar sesión para realizar esta acción.", "../../../vista/login.php", "error"); 
    exit();
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnEditar'])) {
    $objControl->editarHistoria($_POST);
} else {
    header("Location: ./indexEditarHistorialPaciente.php");
    exit();
}
?>