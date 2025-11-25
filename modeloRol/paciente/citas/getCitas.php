<?php
// FILE: getCitas.php

session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlCitas.php'); // Incluye el controlCitas actualizado

$objMensaje = new mensajeSistema();
$objControl = new controlCitas(); // Controlador/Invoker

if (isset($_GET['action']) && isset($_GET['id'])) {
    $idCita = $_GET['id'];
    $action = $_GET['action'];
    $idUsuario = $_SESSION['id_usuario'] ?? null;
    
    if (!is_numeric($idCita) || !$idUsuario) {
        $objMensaje->mensajeSistemaShow("Datos inválidos.", "./indexCitas.php", "error");
        exit;
    }

    switch ($action) {
        case 'cancelar':
            // Llamada al Invoker, que internamente usa Command y Chain of Responsibility
            $objControl->cancelarCita($idCita, $idUsuario); 
            break;
        default:
            $objMensaje->mensajeSistemaShow("Acción no válida.", "./indexCitas.php", "error");
            break;
    }
} else {
    $objMensaje->mensajeSistemaShow("Acceso denegado.", "./indexCitas.php", "error");
}
?>