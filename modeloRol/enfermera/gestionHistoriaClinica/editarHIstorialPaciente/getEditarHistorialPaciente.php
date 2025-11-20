<?php
// Fichero: gestionHistoriaClinica/editarHistorialPaciente/getEditarHistorialPaciente.php

session_start();

include_once('./controlEditarHistorialPaciente.php');
include_once('../../../../shared/mensajeSistema.php');

$objControl = new controlEditarHistorialPaciente();
$objMensaje = new mensajeSistema();

if (!isset($_SESSION['id_usuario'])) {
    $objMensaje->mensajeSistemaShow("Debe iniciar sesión para realizar esta acción.", "../../../vista/login.php", "error"); 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $objControl->editarHistoria($_POST);
    
} else {
    // Si se accede sin POST, redirigir
    header("Location: ../indexHistoriaClinica.php");
    exit();
}
?>