<?php
// Directorio: /controlador/paciente/getPaciente.php

session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('./controlPaciente.php');

// ==========================================================
// ESTRUCTURA DE PATRONES: FRONT CONTROLLER / INVOKER
// ==========================================================

$objControl = new controlPaciente(); 
$objMensaje = new mensajeSistema(); 

// Recolección centralizada de datos
$action = $_GET['action'] ?? null;
$idPaciente = $_GET['id'] ?? null;

if ($action && $idPaciente) {
    $data = [
        'action' => $action, 
        'idPaciente' => (int)$idPaciente
    ];

    try {
        // INVOKER: Delega el trabajo al Contexto/Mediator (controlPaciente)
        $objControl->procesarAccion($data); 
        
    } catch (Exception $e) {
        $objMensaje->mensajeSistemaShow(
            '❌ Error en la solicitud: ' . $e->getMessage(), 
            "./indexTotalPaciente.php", 
            "error"
        );
    }

} else {
    $objMensaje->mensajeSistemaShow("Parámetros de acción incompletos.", "./indexTotalPaciente.php", "error");
}
?>